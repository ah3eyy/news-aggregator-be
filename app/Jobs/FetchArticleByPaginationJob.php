<?php

namespace App\Jobs;

use App\Services\ArticleService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchArticleByPaginationJob implements ShouldQueue
{
    use Queueable;

    public string $url;
    public string $query;
    public int $page = 1;
    public string $provider;

    /**
     * Create a new job instance.
     */
    public function __construct($url, $query, $provider)
    {
        $this->url = $url;
        $this->query = $query;
        $this->provider = $provider;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $hasMorePages = true;
        $retryCount = 0;
        $maxRetries = 2;

        $articleService = app(ArticleService::class);

        while ($hasMorePages) {
            try {

                $response = Http::get($this->url, $this->query . "&page={$this->page}&pageSize=100");

                if ($response->successful()) {

                    $data = $response->json();

                    $formattedData = [];

                    if ($this->provider === 'new_york_times') {
                        $hasMorePages = ($data['response']['meta']['time'] * $this->page) < $data['response']['meta']['hits'];
                        $formattedData = $articleService->formatNewYorkTimesResponse($data['response']['docs']);
                    }

                    if ($this->provider === 'guardian_api') {
                        $hasMorePages = ($data['response']['pageSize'] * $this->page) < $data['response']['total'];
                        $formattedData = $articleService->formatGuardianResponse($data['response']['results']);
                    }

                    $articleService->createArticles($formattedData);

                    $this->page++;
                } else {
                    $hasMorePages = false;
                }
            } catch (\Exception $e) {
                Log::info($e->getMessage());

                // Retry mechanism or break after max retries
//                $retryCount++;
//                if ($retryCount >= $maxRetries) {
//                    Log::error("Max retries reached, stopping fetch process.");
//                    $hasMorePages = false;
//                } else {
//                    Log::info("Retrying... Attempt {$retryCount}/{$maxRetries}");
//                    sleep(10);
//                }
            }
            sleep(60);
        }

    }
}
