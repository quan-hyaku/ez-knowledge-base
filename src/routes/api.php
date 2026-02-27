<?php

use Illuminate\Support\Facades\Route;
use EzKnowledgeBase\Http\Controllers\ApiController;

Route::middleware(['api', 'throttle:' . config('kb.api.rate_limit', 60) . ',1', 'kb.api.auth'])
    ->prefix('api/kb')
    ->group(function () {
        Route::get('/', [ApiController::class, 'home'])->name('kb.api.home');
        Route::get('/categories/{slug}', [ApiController::class, 'category'])->name('kb.api.category');
        Route::get('/categories/{slug}/{article}', [ApiController::class, 'article'])->name('kb.api.article');
        Route::get('/search', [ApiController::class, 'search'])->name('kb.api.search');
    });
