<?php

use App\Http\Controllers\Api\Admin\ComplaintModerationController;
use App\Http\Controllers\Api\Admin\PostModerationController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ReactionController;
use App\Http\Controllers\Api\ShareController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::get('/feed', [FeedController::class, 'index']);
    Route::get('/posts/{post}', [PostController::class, 'show']);

    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::patch('/posts/{post}/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/posts/{post}/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/posts/{post}/reactions', [ReactionController::class, 'store']);
    Route::post('/posts/{post}/complaints', [ComplaintController::class, 'store']);
    Route::post('/posts/{post}/share', [ShareController::class, 'store']);
    Route::get('/share/{slug}', [ShareController::class, 'show']);

    Route::post('/posts', [PostController::class, 'store']);
    Route::patch('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    Route::prefix('admin')->group(function () {
        Route::get('/posts', [PostModerationController::class, 'index']);
        Route::patch('/posts/{post}/status', [PostModerationController::class, 'updateStatus']);

        Route::get('/complaints', [ComplaintModerationController::class, 'index']);
        Route::patch('/complaints/{complaint}/status', [ComplaintModerationController::class, 'updateStatus']);
    });
});
