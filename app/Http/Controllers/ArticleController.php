<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    use ResponseTrait;

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->query();

        $keyword = htmlspecialchars(optional($query)['keyword'] ?? '', ENT_QUOTES, 'UTF-8');
        $date = optional($query)['date'] ? Carbon::parse($query['date']) : null;
        $category = optional($query)['category'] ? explode(',', $query['category']) : [];
        $author = [];
        $source = optional($query)['source'] ? explode(',', $query['source']) : [];
        $pageSize = min(optional($query)['pageSize'] ?? 100, 1000);

        $user = $request->user();
        if ($user) {
            $settings = $user->settings;
            if (!$category) {
                $categoryValue = (clone $settings)->where('key', 'categories')->pluck('value');
                if (count($categoryValue) > 0) $category = $categoryValue[0];
            }
            if (!$author) {
                $authorValue = (clone $settings)->where('key', 'authors')->pluck('value');
                if (count($authorValue) > 0) $author = $authorValue[0];
            }

            if (!$source) {
                $sourceValue = (clone $settings)->where('key', 'sources')->pluck('value');
                if (count($sourceValue) > 0) $source = $sourceValue[0];
            }
        }

        $article = Article::query()
            ->when($keyword, function ($query) use ($keyword) {
                return $query
                    ->where('title', 'like', "%$keyword%")
                    ->orWhere('content', 'like', "%$keyword%")
                    ->orWhere('description', 'like', "%$keyword%");
            })
            ->when($date, function ($query) use ($date) {
                return $query->whereDate('published_date', $date);
            })
            ->when($category, function ($query) use ($category) {
                return $query->orWhereIn('category', $category);
            })
            ->when($source, function ($query) use ($source) {
                return $query->orWhereIn('source', $source);
            })
            ->when($author, function ($query) use ($author) {
                return $query->orWhereIn('author', $author);
            })
            ->paginate($pageSize);

        return $this->successResponse($article, 'Article retrieved successfully.');
    }


}
