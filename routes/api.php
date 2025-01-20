<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UtilController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::prefix('authentication')->group(function () {
    Route::post('/register', [AuthenticationController::class, 'create']);
    Route::post('/login', [AuthenticationController::class, 'login']);
});

Route::group(['prefix' => 'articles',], function () {
    Route::get('/', [ArticleController::class, 'index'])->middleware('authenticateBearerToken');
});

Route::group(['prefix' => 'util'], function () {
    Route::get('/categories', [UtilController::class, 'categories']);
    Route::get('/authors', [UtilController::class, 'authors']);
    Route::get('/sources', [UtilController::class, 'sources']);
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/save-preference', [UserController::class, 'savePreference']);
    });
});
