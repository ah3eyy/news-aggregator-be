<?php

namespace App\Jobs;

use App\Services\ArticleService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchArticleByCategoryJob implements ShouldQueue
{
    use Queueable;

    public string $url;
    public string $query;
    public string $provider;
    public array $category;

    /**
     * Create a new job instance.
     */
    public function __construct($url, $query, $provider, $category)
    {
        $this->url = $url;
        $this->query = $query;
        $this->provider = $provider;
        $this->category = $category;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $articleService = app(ArticleService::class);
        foreach ($this->category as $category) {
            try {
                $response = Http::get($this->url, $this->query . "&category={$category}");
                if ($response->successful()) {

                    $data = $response->json();

                    $formattedData = $articleService->formatNewsApiResponse($data['articles'], $category);

                    $articleService->createArticles($formattedData);
                } else {
                    Log::info('Failed to fetch articles');
                }
            } catch
            (\Exception $e) {
                Log::info($e->getMessage());
            }
        }
    }
}
