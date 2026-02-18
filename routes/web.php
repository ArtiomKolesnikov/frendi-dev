<?php

use App\Http\Controllers\Web\FeedController;
use App\Http\Controllers\Web\PostController as WebPostController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', FeedController::class)->name('feed');

Route::get('/posts/create', [WebPostController::class, 'create'])->name('posts.create');
Route::post('/posts', [WebPostController::class, 'store'])->name('posts.store');
Route::get('/posts/{post}/edit', [WebPostController::class, 'edit'])->name('posts.edit');
Route::put('/posts/{post}', [WebPostController::class, 'update'])->name('posts.update');
Route::delete('/posts/{post}', [WebPostController::class, 'destroy'])->name('posts.destroy');

// Share public page (single post view within feed layout)
use App\Http\Controllers\Web\ShareController as WebShareController;
use App\Http\Controllers\Web\OgController;
Route::get('/share/{slug}', [WebShareController::class, 'show'])->name('web.share.show');
// Dynamic OG image endpoints (cached by CDN/clients)
Route::get('/og/default.jpg', [OgController::class, 'default'])->name('og.default');
Route::get('/og/post/{slug}.jpg', [OgController::class, 'postBySlug'])->name('og.post');

// User auth
use App\Http\Controllers\Web\Auth\UserAuthController;
Route::get('/register', [UserAuthController::class, 'showRegister'])->name('user.register');
Route::post('/register', [UserAuthController::class, 'register'])->name('user.register.submit');
Route::get('/login', [UserAuthController::class, 'showLogin'])->name('user.login');
Route::post('/login', [UserAuthController::class, 'login'])->name('user.login.submit');
Route::post('/logout', [UserAuthController::class, 'logout'])->name('user.logout');
Route::get('/profile', function(){
    if (!Auth::check() && !session()->has('admin_id')) {
        return redirect()->route('user.login');
    }
    $admin = session()->has('admin_id') ? \App\Models\Admin::find(session('admin_id')) : null;
    return view('auth.profile', [ 'title' => 'Perfil', 'admin' => $admin ]);
})->name('user.profile');

// Legal pages (static)
Route::view('/legal/cookies', 'legal.cookies')->name('legal.cookies');
Route::view('/legal/privacy', 'legal.privacy')->name('legal.privacy');
Route::view('/legal/terms', 'legal.terms')->name('legal.terms');

// Admin
use App\Http\Controllers\Web\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\MetricsController as AdminMetricsController;
use App\Http\Controllers\Web\Admin\ContestController as AdminContestController;

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
Route::get('/admin', AdminDashboardController::class)->name('admin.dashboard');
Route::get('/admin/metrics', AdminMetricsController::class)->name('admin.metrics');
Route::get('/admin/contest', [AdminContestController::class, 'index'])->name('admin.contest');
Route::post('/admin/contest', [AdminContestController::class, 'store'])->name('admin.contest.store');
use App\Http\Controllers\Web\Admin\PostModerationController;
use App\Http\Controllers\Api\CommentController as ApiCommentController;
Route::post('/admin/posts/{post}/approve', [PostModerationController::class, 'approve'])->name('admin.posts.approve');

// Admin comment management via web (session)
Route::patch('/admin/posts/{post}/comments/{comment}', [ApiCommentController::class, 'update'])->name('admin.comments.update');
Route::delete('/admin/posts/{post}/comments/{comment}', [ApiCommentController::class, 'destroy'])->name('admin.comments.destroy');

// Telegram cache busting
Route::get('/telegram-cache-bust', function () {
    return response()->view('feed.index', [
        'title' => 'Frendi',
        'posts' => collect(),
        'winners' => collect(),
        'authorToken' => null,
        'og' => [
            'title' => 'Frendi',
            'description' => 'Mascotas, rutas y concursos. ¡Únete!',
            'image' => asset('images/og-default.jpg') . '?v=' . time(),
            'url' => url()->current(),
            'type' => 'website',
        ],
    ]);
});
