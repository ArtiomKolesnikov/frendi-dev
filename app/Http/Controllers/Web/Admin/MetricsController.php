<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\ShareEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!$request->session()->has('admin_id')) {
            return redirect()->route('admin.login');
        }

        // Маршруты
        $totalUsers = DB::table('posts')->distinct('author_token')->count('author_token');
        $routeUsers = Post::query()->where('type', Post::TYPE_ROUTE)->distinct('author_token')->count('author_token');
        $routeUsersWithPhoto = Post::query()->where('type', Post::TYPE_ROUTE)->whereHas('media')->distinct('author_token')->count('author_token');
        $percentRouteUsers = $totalUsers ? round($routeUsers / $totalUsers * 100, 1) : 0.0;
        $percentRouteUsersWithPhoto = $totalUsers ? round($routeUsersWithPhoto / $totalUsers * 100, 1) : 0.0;

        // Фото животных
        $totalPetPosts = Post::query()->where('type', Post::TYPE_PET)->count();
        $petsLikesCount = PostReaction::query()->whereHas('post', fn($q) => $q->where('type', Post::TYPE_PET))->where('type','like')->count();
        $petsCommentsCount = Comment::query()->whereHas('post', fn($q) => $q->where('type', Post::TYPE_PET))->count();
        $percentPetsLikes = $totalPetPosts ? round($petsLikesCount / $totalPetPosts * 100, 1) : 0.0;
        $percentPetsComments = $totalPetPosts ? round($petsCommentsCount / $totalPetPosts * 100, 1) : 0.0;

        // Моя собака
        $totalMyDogPosts = Post::query()->where('type', Post::TYPE_MY_DOG)->count();
        $mydogSharesCount = ShareEvent::query()->whereHas('post', fn($q) => $q->where('type', Post::TYPE_MY_DOG))->distinct('author_token')->count('author_token');
        $percentMydogShares = $totalMyDogPosts ? round($mydogSharesCount / $totalMyDogPosts * 100, 1) : 0.0;

        // Конкурсы
        $contestsParticipants = DB::table('posts')->where('type', Post::TYPE_CONTEST)->count();
        $postsViews = DB::table('posts')->whereNotNull('published_at')->count();

        return view('admin.metrics', [
            'title' => 'Metrics',
            'totalUsers' => $totalUsers,
            'routeUsers' => $routeUsers,
            'percentRouteUsers' => $percentRouteUsers,
            'routeUsersWithPhoto' => $routeUsersWithPhoto,
            'percentRouteUsersWithPhoto' => $percentRouteUsersWithPhoto,
            'totalPetPosts' => $totalPetPosts,
            'petsLikesCount' => $petsLikesCount,
            'percentPetsLikes' => $percentPetsLikes,
            'petsCommentsCount' => $petsCommentsCount,
            'percentPetsComments' => $percentPetsComments,
            'totalMyDogPosts' => $totalMyDogPosts,
            'mydogSharesCount' => $mydogSharesCount,
            'percentMydogShares' => $percentMydogShares,
            'contestsParticipants' => $contestsParticipants,
            'postsViews' => $postsViews,
        ]);
    }
} 