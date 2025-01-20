<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleService
{
    public function formatGuardianResponse($response): array
    {
        return array_map(function ($data) {
            return [
                'external_reference' => $data['id'],
                'category' => $data['pillarName'] ?? $data['sectionName'],
                'source' => json_encode([
                    'name' => $data['sectionName'],
                    'id' => $data['sectionId']
                ]),
                'news_url' => $data['webTitle'],
                'image_url' => null,
                'published_date' => $data['webPublicationDate'],
                'title' => $data['webTitle'],
                'description' => null,
                'content' => null,
                'provider' => 'guardian_api',
            ];
        }, $response);
    }

    public function formatNewYorkTimesResponse($response): array
    {
        return array_map(function ($data) {
            return [
                'external_reference' => $data['_id'],
                'category' => $data['section_name'],
                'source' => json_encode([
                    'name' => $data['source'],
                    'id' => Str::slug($data['source'])
                ]),
                'news_url' => $data['web_url'],
                'image_url' => null,
                'published_date' => $data['pub_date'],
                'title' => $data['headline']['main'],
                'description' => $data['abstract'],
                'content' => $data['lead_paragraph'],
                'provider' => 'new_york_times',
                'author' => $data['byline']['original']
            ];
        }, $response);
    }

    public function formatNewsApiResponse($response, $category): array
    {
        return array_map(function ($data) use ($category) {
            return [
                'external_reference' => Str::slug($data['title']),
                'category' => $category,
                'source' => json_encode([
                    'name' => $data['source']['name'],
                    'id' => $data['source']['id']
                ]),
                'news_url' => $data['url'],
                'image_url' => $data['urlToImage'],
                'published_date' => $data['publishedAt'],
                'title' => $data['title'],
                'description' => $data['description'],
                'content' => $data['content'],
                'provider' => 'news_api',
                'author' => $data['author']
            ];
        }, array_filter($response, function ($data) {
            return $data['title'] !== '[Removed]';
        }));
    }

    public function createArticles(array $data): void
    {
        Article::upsert($data, ['external_reference'],
            [
                'external_reference',
                'category',
                'source',
                'news_url',
                'image_url',
                'published_date',
                'title',
                'description',
                'content',
                'provider',
                'author'
            ]
        );
        return;
    }

    public function spoolRecordByCategory($url, $query, $provider, $categories): void
    {
        foreach ($categories as $category) {
            try {
                $response = Http::get($url, $query . "&category={$category}");
                if ($response->successful()) {

                    $data = $response->json();

                    $formattedData = $this->formatNewsApiResponse($data['articles'], $category);

                    $this->createArticles($formattedData);
                } else {
                    Log::info('Failed to fetch articles');
                }
            } catch
            (\Exception $e) {
                Log::info($e->getMessage());
            }
        }
    }

    public function spoolRecordByPagination($url, $query, $provider): void
    {
        $page = 1;

        $hasMorePages = true;

        while ($hasMorePages) {
            try {

                $response = Http::get($url, $query . "&page={$page}&pageSize=100");

                if ($response->successful()) {

                    $data = $response->json();

                    $formattedData = [];

                    if ($provider === 'new_york_times') {
                        $hasMorePages = ($data['response']['meta']['time'] * $page) < $data['response']['meta']['hits'];
                        $formattedData = $this->formatNewYorkTimesResponse($data['response']['docs']);
                    }

                    if ($provider === 'guardian_api') {
                        $hasMorePages = ($data['response']['pageSize'] * $page) < $data['response']['total'];
                        $formattedData = $this->formatGuardianResponse($data['response']['results']);
                    }

                    $this->createArticles($formattedData);

                    $page++;
                } else {
                    $hasMorePages = false;
                }
            } catch (\Exception $e) {
                Log::info($e->getMessage());
            }
            sleep(60);
        }
    }

}
