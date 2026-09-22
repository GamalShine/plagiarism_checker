<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PageContentFetcher
{
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function fetch(string $url): string
    {
        if (empty($url) || str_starts_with($url, '#') || str_starts_with($url, 'local://')) {
            return '';
        }

        try {
            $response = Http::timeout(4)
                ->withHeaders([
                    'User-Agent' => self::USER_AGENT,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                ])
                ->get($url);

            if (!$response->successful()) {
                return '';
            }

            $html = $response->body();

            $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
            $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
            $html = preg_replace('/<nav[^>]*>.*?<\/nav>/is', '', $html);
            $html = preg_replace('/<header[^>]*>.*?<\/header>/is', '', $html);
            $html = preg_replace('/<footer[^>]*>.*?<\/footer>/is', '', $html);
            $html = preg_replace('/<aside[^>]*>.*?<\/aside>/is', '', $html);
            $html = strip_tags($html);
            $html = preg_replace('/&[a-z]+;/i', ' ', $html);
            $html = preg_replace('/\s+/u', ' ', $html);

            return mb_substr(trim($html), 0, 5000);
        } catch (\Exception $e) {
            return '';
        }
    }
}
