<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostModerationController extends Controller
{
    public function approve(Request $request, Post $post): RedirectResponse
    {
        if (!$request->session()->has('admin_id')) {
            return redirect()->route('admin.login');
        }

        if ($post->status === Post::STATUS_APPROVED) {
            $post->status = Post::STATUS_PENDING;
            $post->published_at = null;
            $message = 'Пост снят с публикации';
        } else {
            $post->status = Post::STATUS_APPROVED;
            $post->published_at = now();
            $message = 'Пост одобрен';
        }
        $post->save();

        return redirect()->route('admin.dashboard')->with('status', $message);
    }
} 