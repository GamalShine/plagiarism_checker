<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OaiPmhHarvester
{
    private const RESULT_LIMIT = 100; // per request, OAI max 2000

    public function harvest(string $endpoint, string $set = null, string $from = null, string $until = null): array
    {
        $records = [];
        $cursor = 0;

        while (true) {
            $params = [
                'verb' => 'ListRecords',
                'metadataPrefix' => 'oai_dc',
                'resultSize' => self::RESULT_LIMIT,
            ];



            if ($set) $params['set'] = $set;
            if ($from) $params['from'] = $from; // YYYY-MM-DD
            if ($until) $params['until'] = $until; // YYYY-MM-DD
            if ($cursor > 0) $params['resumptionToken'] = $cursor;

            $query = http_build_query($params);
            $fullUrl = rtrim($endpoint, '/') . '?' . $query;

            $response = Http::timeout(120)->get($fullUrl);

            if ($response->failed()) {
                Log::warning("OAI-PMH harvest failed", [
                    'endpoint' => $endpoint,
                    'error' => $response->status() . ' ' . $response->body(),
                ]);
                break;
            }

            $xml = simplexml_load_string($response->body());

            if ($xml === false) {
                Log::warning("OAI-PMH XML parse failed", ['endpoint' => $endpoint]);
                break;
            }

            // Check for error
            if ((string)($xml->error) !== '') {
                Log::warning("OAI-PMH error response", [
                    'endpoint' => $endpoint,
                    'error' => (string)($xml->error),
                ]);
                break;
            }

            // Parse records
            $namespaceOai = 'http://www.openarchives.org/OAI/2.0/';
            $namespaceDc = 'http://purl.org/dc/elements/1.1/';

            $records = $xml->xpath('//oai:ListRecords/oai:record', [
                'oai' => $namespaceOai,
            ]);

            if ($records === [] || $records === false) {
                // Try different namespace handling
                $records = $this->parseRecordsRaw($xml, $namespaceOai, $namespaceDc);
            }

            foreach ($records as $record) {
                try {
                    $metadata = $record->xpath('oai:metadata/oai_dc:dc', [
                        'oai' => $namespaceOai,
                        'dc' => $namespaceDc,
                    ])[0] ?? null;

                    if (!$metadata) continue;

                    $title = $this->safeString($metadata->xpath('dc:title')[0] ?? '');
                    $creator = $this->safeString($metadata->xpath('dc:creator')[0] ?? '');
                    $date = $this->safeString($metadata->xpath('dc:date')[0] ?? '');
                    $identifier = $this->safeString($metadata->xpath('dc:identifier')[0] ?? '');
                    $description = $this->safeString($metadata->xpath('dc:description')[0] ?? '');
                    $type = $this->safeString($metadata->xpath('dc:type')[0] ?? '');

                    // Skip jika title kosong
                    if (trim($title) === '') continue;

                    $recordsCollection[] = [
                        'title' => $title,
                        'authors' => $creator,
                        'year' => $date,
                        'url' => $identifier,
                        'abstract' => $description,
                        'type' => $type,
                        'repository' => parse_url($endpoint, PHP_URL_HOST) ?? $endpoint,
                        'oai_metadata' => $metadata->asXML(),
                    ];
                } catch (\Exception $e) {
                    Log::warning("Error parsing OAI record", ['error' => $e->getMessage()]);
                    continue;
                }
            }

            // Check for resumption token
            $resumptionToken = (string)($xml->xpath('//oai:resumptionToken')[0] ?? '');
            if ($resumptionToken === '') {
                // No more records
                break;
            }

            $cursor = $resumptionToken;
        }

        return $recordsCollection ?? [];
    }

    private function parseRecordsRaw($xml, string $nsOai, string $nsDc): array
    {
        // Fallback parsing method
        $records = [];
        $recordNodes = $xml->xpath('//oai:record');

        if ($recordNodes === null || $recordNodes === []) {
            return $records;
        }

        foreach ($recordNodes as $recordNode) {
            try {
                $metadata = $recordNode->xpath('oai:metadata/oai_dc:dc');
                if (!$metadata || empty($metadata)) continue;

                $md = $metadata[0];
                $title = $this->safeString($md->xpath('dc:title')?->[0] ?? '');
                $creator = $this->safeString($md->xpath('dc:creator')?->[0] ?? '');
                $date = $this->safeString($md->xpath('dc:date')?->[0] ?? '');
                $identifier = $this->safeString($md->xpath('dc:identifier')?->[0] ?? '');
                $description = $this->safeString($md->xpath('dc:description')?->[0] ?? '');
                $type = $this->safeString($md->xpath('dc:type')?->[0] ?? '');

                if (trim($title) === '') continue;

                $records[] = [
                    'title' => $title,
                    'authors' => $creator,
                    'year' => $date,
                    'url' => $identifier,
                    'abstract' => $description,
                    'type' => $type,
                    'repository' => '',
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return $records;
    }

    private function safeString($value): string
    {
        return trim((string)($value ?? ''));
    }
}
