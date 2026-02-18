<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Support\ClientContext;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cookie;

class ShareController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $authorToken = ClientContext::token($request);
        $deviceFingerprint = ClientContext::fingerprint($request);

        if (!$request->hasCookie('frendi_token')) {
            Cookie::queue('frendi_token', $authorToken, 60 * 24 * 365);
        }
        if (!$request->hasCookie('frendi_fingerprint')) {
            Cookie::queue('frendi_fingerprint', $deviceFingerprint, 60 * 24 * 365);
        }

        $post = Post::query()->where('share_slug', $slug)->firstOrFail();

        if ($post->status !== Post::STATUS_APPROVED && !hash_equals($post->author_token ?? '', $authorToken)) {
            abort(404);
        }

        $post->load('media');

        $posts = new LengthAwarePaginator([$post], 1, 10, 1, [
            'path' => url()->current(),
        ]);

        $cover = $post->meta['avatar_path'] ?? ($post->media->first()->path ?? null);
        
        // Используем первую картинку поста для OG, если есть, иначе стандартную
        $ogImage = asset('images/og-default.jpg') . '?v=' . env('CACHE_VERSION', 1);
        $ogImageWidth = 1200;
        $ogImageHeight = 630;
        
        if ($post->media->isNotEmpty()) {
            $firstImage = $post->media->first();
            if ($firstImage && $firstImage->path) {
                $imagePath = 'storage/' . $firstImage->path;
                // Проверяем что файл существует
                if (file_exists(public_path($imagePath))) {
                    $ogImage = asset($imagePath);
                    // Получаем реальные размеры изображения
                    $imageInfo = getimagesize(public_path($imagePath));
                    if ($imageInfo) {
                        $ogImageWidth = $imageInfo[0];
                        $ogImageHeight = $imageInfo[1];
                    }
                }
            }
        }
        
        $og = [
            'title' => $post->title ?: 'Frendi — publicación',
            'description' => str($post->body ?? '')->limit(140)->toString() ?: 'Mascotas, rutas y concursos. ¡Únete!',
            'image' => $ogImage,
            'image_width' => $ogImageWidth,
            'image_height' => $ogImageHeight,
            'url' => url()->current(),
            'type' => 'article',
        ];

        return response()->view('feed.index', [
            'title' => $post->title ?: 'Frendi',
            'posts' => $posts,
            'authorToken' => $authorToken,
            'og' => $og,
        ]);
    }
} 