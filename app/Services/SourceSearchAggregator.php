<?php

namespace App\Services;

use App\Models\UserSetting;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SourceSearchAggregator
{
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    private const TIMEOUT = 6;
    private const RESULT_LIMIT = 5;

    private const REPOSITORY_SEARCH_LIMIT = 20; // max repo to search per query

    public const REPOSITORY_PRIORITY = [
        'internet'         => 1,
        'web'              => 1,
        'wikipedia'        => 2,
        'google_scholar'   => 3,
        'elsevier'         => 4,
        'semantic_scholar' => 5,
        'europe_pmc'       => 6,
        'plos'             => 7,
        'gutenberg'        => 8,
        'publications'     => 9,
        'openalex'         => 9,
        'crossref'         => 10,
        'crossref_posted'  => 11,
        'submitted_works'  => 12,
    ];

    public function __construct(
        private PageContentFetcher $contentFetcher,
    ) {}

    public function searchAll(string $query, array $selectedSources = [], ?UserSetting $settings = null): array
    {
        $query = mb_substr($query, 0, 150);
        $results = [];

        $responses = Http::pool(function (Pool $pool) use ($query, $settings) {
            $pool->as('ddg')
                ->timeout(self::TIMEOUT)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get('https://html.duckduckgo.com/html/', ['q' => mb_substr($query, 0, 200)]);

            $pool->as('wiki')
                ->timeout(self::TIMEOUT)
                ->get('https://en.wikipedia.org/w/api.php', [
                    'action' => 'query',
                    'list' => 'search',
                    'srsearch' => mb_substr($query, 0, 100),
                    'utf8' => '',
                    'format' => 'json',
                    'srlimit' => self::RESULT_LIMIT,
                ]);

            if ($settings?->serpapi_key) {
                $pool->as('scholar')
                    ->timeout(self::TIMEOUT)
                    ->get('https://serpapi.com/search.json', [
                        'engine' => 'google_scholar',
                        'q' => $query,
                        'num' => self::RESULT_LIMIT,
                        'api_key' => $settings->serpapi_key,
                    ]);
            } else {
                $pool->as('scholar_direct')
                    ->timeout(self::TIMEOUT)
                    ->withHeaders(['User-Agent' => self::USER_AGENT])
                    ->get('https://scholar.google.com/scholar', [
                        'q' => $query,
                        'hl' => 'en',
                    ]);
            }

            if ($settings?->elsevier_enabled && $settings?->elsevier_api_key) {
                $searchQuery = urlencode($query);
                $pool->as('elsevier')
                    ->timeout(self::TIMEOUT)
                    ->withHeaders(['X-ELS-APIKey' => $settings->elsevier_api_key, 'Accept' => 'application/json'])
                    ->get("https://api.elsevier.com/content/search/scopus?query=TITLE-ABS-KEY({$searchQuery})&count=" . self::RESULT_LIMIT);
            }

            $pool->as('openalex')
                ->timeout(self::TIMEOUT)
                ->withHeaders(['User-Agent' => 'PlagCheckPro/1.0 (mailto:admin@plagcheck.test)'])
                ->get('https://api.openalex.org/works', [
                    'search' => $query,
                    'per_page' => self::RESULT_LIMIT,
                    'mailto' => 'checker@plagcheck.test',
                ]);

            $searchQuery = urlencode($query);
            $pool->as('arxiv')
                ->timeout(self::TIMEOUT)
                ->get("https://export.arxiv.org/api/query?search_query=all:{$searchQuery}&max_results=" . self::RESULT_LIMIT);

            $pool->as('crossref')
                ->timeout(self::TIMEOUT)
                ->withHeaders(['User-Agent' => 'PlagCheckPro/1.0 (mailto:admin@plagcheck.test)'])
                ->get('https://api.crossref.org/works', [
                    'query' => $query,
                    'rows' => self::RESULT_LIMIT,
                ]);

            $pool->as('crossref_posted')
                ->timeout(self::TIMEOUT)
                ->withHeaders(['User-Agent' => 'PlagCheckPro/1.0 (mailto:admin@plagcheck.test)'])
                ->get('https://api.crossref.org/works', [
                    'query' => $query,
                    'rows' => self::RESULT_LIMIT,
                    'filter' => 'type:posted-content',
                ]);

            $pool->as('core')
                ->timeout(self::TIMEOUT)
                ->get('https://api.core.ac.uk/v3/search/works', [
                    'q'     => $query,
                    'limit' => self::RESULT_LIMIT,
                ]);

            $pool->as('semantic_scholar')
                ->timeout(self::TIMEOUT)
                ->get('https://api.semanticscholar.org/graph/v1/paper/search', [
                    'query'  => mb_substr($query, 0, 100),
                    'limit'  => self::RESULT_LIMIT,
                    'fields' => 'title,abstract,url',
                ]);

            $pool->as('europe_pmc')
                ->timeout(self::TIMEOUT)
                ->get('https://www.ebi.ac.uk/europepmc/webservices/rest/search', [
                    'query'      => mb_substr($query, 0, 100),
                    'format'     => 'json',
                    'resultType' => 'lite',
                    'pageSize'   => self::RESULT_LIMIT,
                ]);

            $pool->as('plos')
                ->timeout(self::TIMEOUT)
                ->get('https://api.plos.org/search', [
                    'q'    => 'title:"' . mb_substr($query, 0, 100) . '" OR abstract:"' . mb_substr($query, 0, 100) . '"',
                    'fl'   => 'id,title_display,abstract',
                    'rows' => self::RESULT_LIMIT,
                    'wt'   => 'json',
                ]);

            $pool->as('gutenberg')
                ->timeout(self::TIMEOUT)
                ->get('https://gutendex.com/books', [
                    'search' => mb_substr($query, 0, 50),
                ]);
        });

        if (isset($responses['ddg']) && !($responses['ddg'] instanceof \Throwable) && $responses['ddg']->successful()) {
            $results = array_merge($results, $this->parseDuckDuckGo($responses['ddg']->body()));
        }

        if (isset($responses['wiki']) && !($responses['wiki'] instanceof \Throwable) && $responses['wiki']->successful()) {
            $results = array_merge($results, $this->parseWikipedia($responses['wiki']->json('query.search', [])));
        }

        if (isset($responses['scholar']) && !($responses['scholar'] instanceof \Throwable) && $responses['scholar']->successful()) {
            $results = array_merge($results, $this->parseGoogleScholar($responses['scholar']->json('organic_results', [])));
        }

        if (isset($responses['scholar_direct']) && !($responses['scholar_direct'] instanceof \Throwable) && $responses['scholar_direct']->successful()) {
            $results = array_merge($results, $this->parseGoogleScholarDirect($responses['scholar_direct']->body()));
        }

        if (isset($responses['elsevier']) && !($responses['elsevier'] instanceof \Throwable) && $responses['elsevier']->successful()) {
            $results = array_merge($results, $this->parseElsevier($responses['elsevier']->json('search-results.entry', [])));
        }

        if (isset($responses['openalex']) && !($responses['openalex'] instanceof \Throwable) && $responses['openalex']->successful()) {
            $results = array_merge($results, $this->parseOpenAlex($responses['openalex']->json('results', [])));
        }

        if (isset($responses['arxiv']) && !($responses['arxiv'] instanceof \Throwable) && $responses['arxiv']->successful()) {
            $results = array_merge($results, $this->parseArxiv($responses['arxiv']->body()));
        }

        if (isset($responses['crossref']) && !($responses['crossref'] instanceof \Throwable) && $responses['crossref']->successful()) {
            $results = array_merge($results, $this->parseCrossref($responses['crossref']->json('message.items', []), false));
        }

        if (isset($responses['crossref_posted']) && !($responses['crossref_posted'] instanceof \Throwable) && $responses['crossref_posted']->successful()) {
            $results = array_merge($results, $this->parseCrossref($responses['crossref_posted']->json('message.items', []), true));
        }

        if (isset($responses['core']) && !($responses['core'] instanceof \Throwable) && $responses['core']->successful()) {
            $results = array_merge($results, $this->parseCore($responses['core']->json('results', [])));
        }

        // 4 sumber baru
        if (isset($responses['semantic_scholar']) && !($responses['semantic_scholar'] instanceof \Throwable) && $responses['semantic_scholar']->successful()) {
            $results = array_merge($results, $this->parseSemanticScholar($responses['semantic_scholar']->json('data', [])));
        }

        if (isset($responses['europe_pmc']) && !($responses['europe_pmc'] instanceof \Throwable) && $responses['europe_pmc']->successful()) {
            $results = array_merge($results, $this->parseEuropePmc($responses['europe_pmc']->json('resultList.result', [])));
        }

        if (isset($responses['plos']) && !($responses['plos'] instanceof \Throwable) && $responses['plos']->successful()) {
            $results = array_merge($results, $this->parsePlos($responses['plos']->json('response.docs', [])));
        }

        if (isset($responses['gutenberg']) && !($responses['gutenberg'] instanceof \Throwable) && $responses['gutenberg']->successful()) {
            $results = array_merge($results, $this->parseGutenberg($responses['gutenberg']->json('results', [])));
        }

        return $this->deduplicate($results);
    }

    public function resolveContent(array $source, bool $fetchWeb = true): string
    {
        if (!empty($source['content']) && mb_strlen($source['content']) > 50) {
            return $source['content'];
        }

        if (!$fetchWeb) {
            return $source['content'] ?? '';
        }

        $fetchable = in_array($source['repository'], ['web', 'google_scholar', 'elsevier'], true);

        if ($fetchable && !empty($source['url'])) {
            $fetched = $this->contentFetcher->fetch($source['url']);
            if (mb_strlen($fetched) > 50) {
                return $fetched;
            }
        }

        return $source['content'] ?? '';
    }

    private function deduplicate(array $results): array
    {
        $seen = [];
        $unique = [];

        foreach ($results as $item) {
            $key = $item['url'] ?? $item['title'] ?? null;
            if (!$key || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $item;
        }

        return $unique;
    }

    private function parseDuckDuckGo(string $body): array
    {
        $results = [];
        preg_match_all('/uddg=([^"&]+)/', $body, $matches);
        $urls = [];

        foreach ($matches[1] ?? [] as $encoded) {
            $url = urldecode($encoded);
            if (str_starts_with($url, 'http') && !str_contains($url, 'duckduckgo.com')) {
                $urls[] = $url;
            }
        }

        foreach (array_slice(array_unique($urls), 0, 8) as $url) {
            $host = parse_url($url, PHP_URL_HOST) ?: $url;
            $results[] = $this->hit('internet', 'Internet', $host, $url);
        }

        return $results;
    }

    private function parseWikipedia(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            $title = $item['title'] ?? 'Untitled';
            $snippet = strip_tags($item['snippet'] ?? '');
            $url = 'https://en.wikipedia.org/wiki/' . str_replace(' ', '_', $title);
            $results[] = $this->hit('wikipedia', 'Wikipedia', $title . ' - Wikipedia', $url, trim($title . '. ' . $snippet));
        }

        return $results;
    }

    private function parseGoogleScholar(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            $title = $item['title'] ?? 'Untitled';
            $snippet = $item['snippet'] ?? '';
            $pubInfo = $item['publication_info']['summary'] ?? '';
            $content = trim(implode('. ', array_filter([$title, $snippet, $pubInfo])));
            $url = $item['link'] ?? ($item['resources'][0]['link'] ?? 'https://scholar.google.com');
            $results[] = $this->hit('google_scholar', 'Google Scholar', $title, $url, $content ?: null);
        }

        return $results;
    }

    private function parseGoogleScholarDirect(string $body): array
    {
        $results = [];
        // Basic regex parsing for Google Scholar HTML
        preg_match_all('/<h3 class="gs_rt">.*?<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a><\/h3>(?:.*?<div class="gs_a">(.*?)<\/div>)?(?:.*?<div class="gs_rs">(.*?)<\/div>)?/s', $body, $matches, PREG_SET_ORDER);

        foreach (array_slice($matches, 0, self::RESULT_LIMIT) as $match) {
            $url = $match[1] ?? 'https://scholar.google.com';
            $title = strip_tags($match[2] ?? 'Untitled');
            $pubInfo = strip_tags($match[3] ?? '');
            $snippet = strip_tags($match[4] ?? '');
            
            $content = trim(implode('. ', array_filter([$title, $snippet, $pubInfo])));
            $results[] = $this->hit('google_scholar', 'Google Scholar', $title, $url, $content ?: null);
        }

        return $results;
    }

    private function parseElsevier(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            $title = $item['dc:title'] ?? 'Untitled';
            $description = $item['dc:description'] ?? '';
            $pubName = $item['prism:publicationName'] ?? '';
            $content = trim(implode('. ', array_filter([$title, $description, $pubName])));

            $link = $item['prism:url'] ?? 'https://www.scopus.com';
            foreach ($item['link'] ?? [] as $linkItem) {
                if (($linkItem['@ref'] ?? '') === 'scopus' && !empty($linkItem['@href'])) {
                    $link = $linkItem['@href'];
                    break;
                }
            }

            $results[] = $this->hit('elsevier', 'Elsevier (Scopus)', $title, $link, $content ?: null);
        }

        return $results;
    }

    private function parseOpenAlex(array $works): array
    {
        $results = [];

        foreach ($works as $work) {
            $title = $work['title'] ?? $work['display_name'] ?? 'Untitled';
            $abstract = $this->reconstructAbstract($work['abstract_inverted_index'] ?? null);
            $content = trim(implode('. ', array_filter([$title, $abstract])));
            $doi = $work['doi'] ?? null;
            $url = $doi ? 'https://doi.org/' . ltrim($doi, 'https://doi.org/') : ($work['id'] ?? '#');
            $results[] = $this->hit('publications', 'Publications', $title, $url, $content ?: null);
        }

        return $results;
    }

    private function parseArxiv(string $body): array
    {
        $results = [];
        preg_match_all('/<entry>([\s\S]*?)<\/entry>/', $body, $entries);

        foreach ($entries[1] ?? [] as $block) {
            preg_match('/<title[^>]*>([\s\S]*?)<\/title>/', $block, $titleMatch);
            preg_match('/<summary[^>]*>([\s\S]*?)<\/summary>/', $block, $summaryMatch);
            preg_match('/<id[^>]*>([\s\S]*?)<\/id>/', $block, $idMatch);

            $title = trim(preg_replace('/\s+/u', ' ', $titleMatch[1] ?? 'Untitled'));
            $summary = trim(preg_replace('/\s+/u', ' ', $summaryMatch[1] ?? ''));
            $url = trim($idMatch[1] ?? '');
            $content = trim($title . '. ' . $summary);

            if ($url) {
                $results[] = $this->hit('publications', 'Publications', $title, $url, $content);
            }
        }

        return $results;
    }

    private function parseCrossref(array $items, bool $postedContent): array
    {
        $results = [];
        $repository = $postedContent ? 'crossref_posted' : 'crossref';
        $label = $postedContent ? 'Crossref Posted' : 'Crossref Published';

        foreach ($items as $item) {
            $title = is_array($item['title'] ?? null) ? ($item['title'][0] ?? 'Untitled') : ($item['title'] ?? 'Untitled');
            $abstract = strip_tags($item['abstract'] ?? '');
            $content = trim($title . '. ' . $abstract);
            $url = $item['URL'] ?? ($item['DOI'] ? 'https://doi.org/' . $item['DOI'] : '#');
            $results[] = $this->hit($repository, $label, $title, $url, $content ?: null);
        }

        return $results;
    }

    private function parseCore(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            $title    = $item['title'] ?? 'Untitled';
            $abstract = $item['abstract'] ?? '';
            $content  = trim($title . '. ' . $abstract);
            $url      = $item['downloadUrl'] ?? ($item['doi'] ? 'https://doi.org/' . $item['doi'] : '#');
            $results[] = $this->hit('publications', 'Publications (CORE)', $title, $url, $content ?: null);
        }

        return $results;
    }

    private function parseSemanticScholar(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            $title    = $item['title'] ?? 'Untitled';
            $abstract = $item['abstract'] ?? '';
            $content  = trim(implode('. ', array_filter([$title, $abstract])));
            $url      = $item['url'] ?? ('https://www.semanticscholar.org/paper/' . ($item['paperId'] ?? ''));
            $results[] = $this->hit('semantic_scholar', 'Semantic Scholar', $title, $url, $content ?: null);
        }

        return $results;
    }

    private function parseEuropePmc(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            $title    = $item['title'] ?? 'Untitled';
            $abstract = $item['abstractText'] ?? '';
            $content  = trim(implode('. ', array_filter([$title, $abstract])));
            $source   = $item['source'] ?? 'MED';
            $pmid     = $item['pmid'] ?? ($item['id'] ?? '');
            $url      = "https://europepmc.org/article/{$source}/{$pmid}";
            $results[] = $this->hit('europe_pmc', 'Europe PMC', $title, $url, $content ?: null);
        }

        return $results;
    }

    private function parsePlos(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            $title    = $item['title_display'] ?? 'Untitled';
            $abstract = is_array($item['abstract'] ?? null) ? ($item['abstract'][0] ?? '') : ($item['abstract'] ?? '');
            $content  = trim(implode('. ', array_filter([$title, $abstract])));
            $id       = $item['id'] ?? '';
            $url      = $id ? "https://journals.plos.org/plosone/article?id={$id}" : '#';
            $results[] = $this->hit('plos', 'PLOS (Public Library of Science)', $title, $url, $content ?: null);
        }

        return $results;
    }

    private function parseGutenberg(array $items): array
    {
        $results = [];

        foreach (array_slice($items, 0, 3) as $item) {
            $title = ($item['title'] ?? 'Untitled') . ' (Book)';
            $id    = $item['id'] ?? '';
            $url   = $id ? "https://www.gutenberg.org/ebooks/{$id}" : '#';
            // Gutenberg tidak return full text — hanya judul, sesuai Node.js aslinya
            $results[] = $this->hit('gutenberg', 'Project Gutenberg (Books)', $title, $url, $item['title'] ?? null);
        }

        return $results;
    }

    private function reconstructAbstract(?array $invertedIndex): string
    {
        if (!$invertedIndex) {
            return '';
        }

        $words = [];
        foreach ($invertedIndex as $word => $positions) {
            foreach ($positions as $pos) {
                $words[$pos] = $word;
            }
        }

        ksort($words);

        return implode(' ', $words);
    }

    public function searchRepositories(string $query, ?UserSetting $settings = null): array
    {
        $registry = app(RepositoryRegistry::class);
        $harvester = app(OaiPmhHarvester::class);

        $endpoints = $registry->getEndpoints();
        $results = [];

        // Ambil sample repo (biar cepat, tidak semua)
        $searchLimit = min(self::REPOSITORY_SEARCH_LIMIT, count($endpoints));
        $selectedIndexes = array_rand($endpoints, $searchLimit);
        $selected = is_array($selectedIndexes) ? array_values($selectedIndexes) : array_keys($endpoints, $selectedIndexes, true);

        // Pastikan kita punya indeks yang valid
        $selected = array_values(array_filter($selected, function ($idx) use ($endpoints) {
            return isset($endpoints[$idx]);
        }));

        $queryLower = strtolower($query);

        foreach ($selected as $idx) {
            $endpoint = $endpoints[$idx];
            $endpointUrl = $endpoint['url'] ?? '';

            if (empty($endpointUrl)) continue;

            try {
                $records = $harvester->harvest($endpointUrl);

                foreach ($records as $record) {
                    // Filter berdasarkan kemiripan query
                    $title = strtolower($record['title'] ?? '');
                    $abstract = strtolower($record['abstract'] ?? '');

                    $score = 0;
                    if (str_contains($title, $queryLower)) $score += 10;
                    if (str_contains($abstract, $queryLower)) $score += 5;
                    if (str_contains($title, mb_substr($queryLower, 0, 50))) $score += 3;

                    if ($score > 0) {
                        $results[] = $this->hit(
                            'repository',
                            $endpoint['name'] ?? 'Repository',
                            $record['title'],
                            $record['url'],
                            trim($record['title'] . '. ' . ($record['abstract'] ?? '')),
                            ['score' => $score, 'year' => $record['year'] ?? '', 'type' => $record['type'] ?? '']
                        );

                        if (count($results) >= self::RESULT_LIMIT) {
                            usort($results, function ($a, $b) {
                                $scoreA = $a['extra']['score'] ?? 0;
                                $scoreB = $b['extra']['score'] ?? 0;
                                return $scoreB <=> $scoreA;
                            });
                            return array_values($results);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Repository search failed for endpoint {$endpointUrl}", ['error' => $e->getMessage()]);
                continue;
            }
        }

        // Sort by score descending
        usort($results, function ($a, $b) {
            $scoreA = $a['extra']['score'] ?? 0;
            $scoreB = $b['extra']['score'] ?? 0;
            return $scoreB <=> $scoreA;
        });

        return array_values($results);
    }

    private function hit(string $repository, string $label, string $title, string $url, ?string $content = null, ?array $extra = []): array
    {
        return [
            'repository' => $repository,
            'repositoryLabel' => $label,
            'title' => $title,
            'url' => $url,
            'content' => $content,
            'extra' => $extra,
        ];
    }
}
