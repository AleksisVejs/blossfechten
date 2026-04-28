<?php

use App\Http\Controllers\Api\Admin\MemberAdminController;
use App\Http\Controllers\Api\Admin\PageAdminController;
use App\Http\Controllers\Api\Admin\ForumPostAdminController;
use App\Http\Controllers\Api\Admin\TrainingAdminController;
use App\Http\Controllers\Api\Admin\UserAdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\TrainingController;
use App\Http\Controllers\Api\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn() => ['ok' => true]);

// Auth (session-based SPA auth needs web middleware)
Route::middleware('web')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });
});

// Public content
Route::get('/content/pages', [ContentController::class, 'pages']);
Route::get('/content/pages/{slug}', [ContentController::class, 'page']);
Route::get('/content/members', [ContentController::class, 'members']);
Route::get('/content/videos', [VideoController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1');
Route::get('/trainings', [TrainingController::class, 'index']);
Route::get('/trainings/{training}', [TrainingController::class, 'show']);
Route::get('/forum', [ForumController::class, 'index']);
Route::get('/forum/{slug}', [ForumController::class, 'show']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/trainings/{training}/register', [TrainingController::class, 'register']);
    Route::delete('/trainings/{training}/register', [TrainingController::class, 'unregister']);
    Route::get('/me/registrations', [TrainingController::class, 'myRegistrations']);
});

// Admin
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/forum-posts', [ForumPostAdminController::class, 'index']);
    Route::post('/forum-posts/upload-cover', [ForumPostAdminController::class, 'uploadCover']);
    Route::post('/forum-posts/upload-inline-image', [ForumPostAdminController::class, 'uploadInlineImage']);
    Route::delete('/forum-posts/uploaded-image', [ForumPostAdminController::class, 'deleteUploadedImage']);
    Route::post('/forum-posts', [ForumPostAdminController::class, 'store']);
    Route::put('/forum-posts/{forumPost}', [ForumPostAdminController::class, 'update']);
    Route::delete('/forum-posts/{forumPost}', [ForumPostAdminController::class, 'destroy']);

    Route::get('/trainings', [TrainingAdminController::class, 'index']);
    Route::post('/trainings', [TrainingAdminController::class, 'store']);
    Route::put('/trainings/{training}', [TrainingAdminController::class, 'update']);
    Route::delete('/trainings/{training}', [TrainingAdminController::class, 'destroy']);
    Route::get('/trainings/{training}/registrations', [TrainingAdminController::class, 'registrations']);

    Route::get('/users', [UserAdminController::class, 'index']);
    Route::put('/users/{user}', [UserAdminController::class, 'update']);

    Route::get('/members', [MemberAdminController::class, 'index']);
    Route::post('/members', [MemberAdminController::class, 'store']);
    Route::put('/members/{member}', [MemberAdminController::class, 'update']);
    Route::delete('/members/{member}', [MemberAdminController::class, 'destroy']);
    Route::post('/members/reorder', [MemberAdminController::class, 'reorder']);
    Route::post('/members/upload-photo', [MemberAdminController::class, 'uploadPhoto']);

    Route::get('/pages', [PageAdminController::class, 'index']);
    Route::get('/pages/{slug}', [PageAdminController::class, 'show']);
    Route::put('/pages/{slug}', [PageAdminController::class, 'upsert']);
});
