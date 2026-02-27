<?php

use Illuminate\Support\Facades\Route;
use EzKnowledgeBase\Http\Controllers\KnowledgeBaseController;
use EzKnowledgeBase\Http\Controllers\SearchController;
use EzKnowledgeBase\Http\Controllers\TicketController;
use EzKnowledgeBase\Http\Middleware\TrackArticleView;

Route::middleware('web')->group(function () {
    Route::get('/help-center', [KnowledgeBaseController::class, 'landing'])->name('kb.landing');
    Route::get('/help-center/categories', [KnowledgeBaseController::class, 'categories'])->name('kb.categories');
    Route::get('/help-center/category/{slug}', [KnowledgeBaseController::class, 'category'])->name('kb.category');
    Route::get('/help-center/{category_slug}/{slug}', [KnowledgeBaseController::class, 'article'])->name('kb.article')->middleware(TrackArticleView::class);
    Route::get('/help-center/search', [SearchController::class, 'search'])->name('kb.search');
    Route::get('/help-center/ticket', [TicketController::class, 'create'])->name('kb.ticket.create');
    Route::post('/help-center/ticket', [TicketController::class, 'store'])->name('kb.ticket.store')->middleware('throttle:3,60');
    Route::post('/help-center/article/{id}/feedback', [KnowledgeBaseController::class, 'feedback'])->name('kb.article.feedback')->middleware('throttle:10,1');
});
