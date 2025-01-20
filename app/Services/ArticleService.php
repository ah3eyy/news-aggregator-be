<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleService
{
    public function formatGuardianResponse($response): array
    {
        return array_map(function ($data) {
            return [
                'external_reference' => $data['id'],
                'category' => $data['pillarName'],
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
                'author' => $data['byline']['original']
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
                'provider' => 'new_york_times'
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
        }, $response);
    }

    public function createArticles(array $data): void
    {
        Article::upsert($data, ['id'],
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

}
