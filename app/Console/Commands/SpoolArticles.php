<?php

namespace App\Console\Commands;

use App\Jobs\FetchArticleByCategoryJob;
use App\Jobs\FetchArticleByPaginationJob;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SpoolArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:spool-article';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public $sources = [
        'news_api' => [
            'url' => 'https://newsapi.org/v2/top-headlines',
            'authKey' => 'apiKey',
            'type' => 'categories',
            'categories' => ['business', 'entertainment', 'general', 'health', 'science', 'sports', 'technology']
        ],
        'guardian_api' => [
            'url' => 'https://content.guardianapis.com/search',
            'authKey' => 'api-key',
            'type' => 'paginated'
        ],
        'new_york_times' => [
            'url' => 'https://api.nytimes.com/svc/search/v2/articlesearch.json',
            'authKey' => 'api-key',
            'type' => 'paginated'
        ]
    ];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        foreach ($this->sources as $provider => $providerUrl) {
            $providerUrl = $this->sources[$provider]['url'];
            $authKey = $this->sources[$provider]['authKey'];
            $token = config("services.news_provider.{$provider}");
            try {
                $queryParams = "?";
                $url = $providerUrl . $queryParams;

                $lastSpooledArticleDate = $this->lastSpooledArticleDate($provider);

                $dateFormat = $this->formatDate($lastSpooledArticleDate, Carbon::now(), $provider);

                if ($provider === 'new_york_times') {
                    $queryParams .= "&begin_date={$dateFormat['from']}&end_date={$dateFormat['to']}";
                } else if ($provider === 'guardian_api') {
                    $queryParams .= "&from-date={$dateFormat['from']}&to-date={$dateFormat['to']}&use-date=published";
                } else  $queryParams .= "&from={$dateFormat['from']}&to={$dateFormat['to']}";

                $queryParams .= "&{$authKey}={$token}";

                if ($this->sources[$provider]['type'] === 'paginated') {
                    FetchArticleByPaginationJob::dispatch($url, $queryParams, $provider);
                }

                if ($this->sources[$provider]['type'] === 'categories') {
                    $categories = $this->sources[$provider]['categories'];
                    FetchArticleByCategoryJob::dispatch($url, $queryParams, $provider, $categories);
                }

            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function formatDate($start_date, $end_date, $provider): array
    {
        $formattedDates = [
            'from' => $start_date->format('Y-m-d'),
            'to' => $end_date->format('Y-m-d')
        ];

        if ($provider === 'new_york_times') {
            $formattedDates['from'] = $start_date->format('Ymd');
            $formattedDates['to'] = $end_date->format('Ymd');
        }

        return $formattedDates;
    }

    public function lastSpooledArticleDate($provide)
    {
        $articles = Article::where('provider', $provide)->orderBy('created_at', 'desc')->first();

        if ($articles) return Carbon::parse($articles->created_at);

        return Carbon::now()->subDays(1);
    }
}
