<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RepositoryRegistry
{
    private const CACHE_KEY = 'oai_registry_endpoints';
    private const CACHE_MINUTES = 4320; // 3 hari

    public function getEndpoints(): array
    {
        return Cache::remember(self::CACHE_KEY, $this->cacheMinutes(), function () {
            $endpoints = [];

            // 1. OpenDOAR API - 5000+ repository worldwide
            $opendoar = Http::timeout(30)->get('https://v2.sherpa.ac.uk/opendoar/api/repositories', [
                'format' => 'json',
                'per_page' => 100,
            ]);

            if ($opendoar->successful()) {
                foreach ($opendoar->json('items', []) as $repo) {
                    if (!empty($repo['oai_url'])) {
                        $endpoints[] = [
                            'url' => $repo['oai_url'],
                            'name' => $repo['name'] ?? 'Unknown Repository',
                            'country' => $repo['country'] ?? '',
                            'type' => 'opendoar',
                            'description' => $repo['description'] ?? '',
                        ];
                    }
                }
            }

            // 2. Manual: Repository Indonesia terkenal (OAI-PMH terdaftar)
            $indonesia = [
                [
                    'url' => 'http://repository.ugm.ac.id/oai2',
                    'name' => 'Repository UGM',
                    'country' => 'ID',
                    'type' => 'manual',
                    'description' => 'Universitas Gadjah Mada - Full text & metadata',
                ],
                [
                    'url' => 'http://eprints.uny.ac.id/oai2',
                    'name' => 'Repository UNY',
                    'country' => 'ID',
                    'type' => 'manual',
                    'description' => 'Universitas Negeri Yogyakarta',
                ],
                [
                    'url' => 'http://repository.ub.ac.id/oai2',
                    'name' => 'Repository UB',
                    'country' => 'ID',
                    'type' => 'manual',
                    'description' => 'Universitas Borobudur',
                ],
                [
                    'url' => 'http://eprints.undip.ac.id/oai2',
                    'name' => 'Repository Undip',
                    'country' => 'ID',
                    'type' => 'manual',
                    'description' => 'Universitas Diponegoro',
                ],
                [
                    'url' => 'http://repository.ipb.ac.id/oai2',
                    'name' => 'Repository IPB',
                    'country' => 'ID',
                    'type' => 'manual',
                    'description' => 'Institut Pertanian Bogor',
                ],
                [
                    'url' => 'http://digilib.its.ac.id/oai2',
                    'name' => 'Repository ITS',
                    'country' => 'ID',
                    'type' => 'manual',
                    'description' => 'Institut Teknologi Surabaya',
                ],
                [
                    'url' => 'http://repository.uin-suka.ac.id/oai2',
                    'name' => 'Repository UIN Sunan Giri',
                    'country' => 'ID',
                    'type' => 'manual',
                    'description' => 'UIN Sunan Giri',
                ],
                [
                    'url' => 'http://eprints.unair.ac.id/oai2',
                    'name' => 'Repository UNAIR',
                    'country' => 'ID',
                    'type' => 'manual',
                    'description' => 'Universitas Airlangga',
                ],
                [
                    'url' => 'http://repository.uns.ac.id/oai2',
                    'name' => 'Repository UN',
                    'country' => 'ID',
                    'type' => 'manual',
                    'description' => 'Universitas Negeri',
                ],
                [
                    'url' => 'http://eprints.umm.ac.id/oai2',
                    'name' => 'Repository UMM',
                    'country' => 'ID',
                    'type' => 'manual',
                    'description' => 'Universitas Muhammadiyah Malang',
                ],
            ];

            foreach ($indonesia as $repo) {
                // Cek apakah sudah ada dari OpenDOAR (by URL)
                $alreadyExists = collect($endpoints)->contains(function ($e) use ($repo) {
                    return $e['url'] === $repo['url'];
                });

                if (!$alreadyExists) {
                    $endpoints[] = $repo;
                }
            }

            // 3. Base (Bielefeld) - 300M+ dari 7000 repo
            $base = Http::timeout(30)->get('https://api.base-search.net/v1/metadata/oaicollections', [
                'q' => 'oai',
                'format' => 'json',
                'limit' => 50,
            ]);

            if ($base->successful()) {
                foreach ($base->json('items', []) as $repo) {
                    $oai = $repo['oai'] ?? null;
                    if ($oai && !collect($endpoints)->contains(function ($e) use ($oai) {
                        return $e['url'] === $oai;
                    })) {
                        $endpoints[] = [
                            'url' => $oai,
                            'name' => $repo['title'] ?? 'BASE Collection',
                            'country' => '',
                            'type' => 'base-search',
                            'description' => 'BASE Bielefeld Collection',
                        ];
                    }
                }
            }

            // Urutkan: Indonesia dulu, lalu alphabet
            usort($endpoints, function ($a, $b) {
                $aType = $a['type'] ?? '';
                $bType = $b['type'] ?? '';

                // Indonesia dulu
                if ($a['type'] === 'manual' && $b['type'] !== 'manual') return -1;
                if ($a['type'] !== 'manual' && $b['type'] === 'manual') return 1;

                // Lalu alphabet
                return strcmp($a['name'] ?? '', $b['name'] ?? '');
            });

            return $endpoints;
        });
    }

    private function cacheMinutes(): int
    {
        // Refresh setiap 3 hari, tapi masih bisa manual update
        return 4320;
    }

    public function getIndonesiaOnly(): array
    {
        $all = $this->getEndpoints();
        return array_values(array_filter($all, function ($e) {
            return $e['country'] === 'ID' || str_contains($e['name'] ?? '', 'Indonesia');
        }));
    }

    public function getInternational(): array
    {
        $all = $this->getEndpoints();
        return array_values(array_filter($all, function ($e) {
            return $e['country'] !== 'ID' && $e['type'] !== 'manual';
        }));
    }
}
