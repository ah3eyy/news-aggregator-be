<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class UtilController extends Controller
{

    use ResponseTrait;

    public function categories(Request $request): \Illuminate\Http\JsonResponse
    {
        $category = Article::all()->groupBy('category')->keys()->toArray();
        return $this->successResponse($category, 'Categories');
    }

    public function authors(Request $request): \Illuminate\Http\JsonResponse
    {
        $author = Article::all()->groupBy('author')->keys()->toArray();
        return $this->successResponse($author, 'Authors');
    }

    public function sources(Request $request): \Illuminate\Http\JsonResponse
    {
        $source = Article::all()->groupBy(function ($article) {
            return $article->source['name'];
        })
            ->keys()
            ->toArray();
        return $this->successResponse($source, 'Sources');
    }

}
