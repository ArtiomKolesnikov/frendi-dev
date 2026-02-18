<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Support\ClientContext;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    private function ensureAuthor(Request $request, Post $post): void
    {
        // Allow admin to manage any post
        if ($request->session()->has('admin_id')) {
            return;
        }

        $authorToken = ClientContext::token($request);
        $deviceFingerprint = ClientContext::fingerprint($request);

        if (!hash_equals($post->author_token ?? '', $authorToken)) {
            abort(403, 'Доступно только автору публикации.');
        }

        if (!$request->hasCookie('frendi_token')) {
            Cookie::queue('frendi_token', $authorToken, 60 * 24 * 365);
        }
        if (!$request->hasCookie('frendi_fingerprint')) {
            Cookie::queue('frendi_fingerprint', $deviceFingerprint, 60 * 24 * 365);
        }
    }

    public function create(Request $request)
    {
        $authorToken = ClientContext::token($request);
        $deviceFingerprint = ClientContext::fingerprint($request);
        
        if (!$request->hasCookie('frendi_token')) {
            Cookie::queue('frendi_token', $authorToken, 60 * 24 * 365);
        }
        if (!$request->hasCookie('frendi_fingerprint')) {
            Cookie::queue('frendi_fingerprint', $deviceFingerprint, 60 * 24 * 365);
        }

        return view('posts.create', [
            'title' => 'New post',
            'asAdmin' => (bool) $request->session()->has('admin_id') && ($request->query('as') === 'admin'),
            'preselectedType' => $request->query('type'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(Post::TYPES)],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'contest_status' => ['nullable', Rule::in(['new', 'past'])],
            'author_display_name' => ['nullable', 'string', 'max:120'],
            'author_contact' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'file', 'image', 'max:4096'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'image', 'max:5120'],
        ]);

        $authorToken = ClientContext::token($request);

        $post = DB::transaction(function () use ($validated, $authorToken, $request) {
            $meta = [];
            if (!empty($validated['contest_status'])) {
                $meta['contest_status'] = $validated['contest_status'];
            }

            $isAdminPost = $request->session()->has('admin_id');
            $post = Post::create([
                'type' => $validated['type'],
                'title' => $validated['title'] ?? null,
                'body' => $validated['body'] ?? null,
                'meta' => $meta ?: null,
                'author_display_name' => $isAdminPost ? 'frendi.com' : ($validated['author_display_name'] ?? null),
                'author_contact' => $isAdminPost ? null : ($validated['author_contact'] ?? null),
                'author_token' => $isAdminPost ? null : $authorToken,
                'is_admin' => $isAdminPost,
                'status' => $isAdminPost ? Post::STATUS_APPROVED : Post::STATUS_PENDING,
                'published_at' => $isAdminPost ? now() : null,
            ]);

            // Save avatar into meta
            if ($request->file('avatar') instanceof UploadedFile) {
                $file = $request->file('avatar');
                $avatarPath = $file->storeAs('posts/'.$post->uuid, 'avatar.'.$file->getClientOriginalExtension(), 'public');
                $post->meta = array_merge($post->meta ?? [], ['avatar_path' => $avatarPath]);
                $post->save();
            }

            $this->storeMedia($post, $request->file('media', []));

            return $post;
        });

        return redirect()->route('feed')->with('status', 'Пост отправлен на модерацию');
    }

    public function edit(Request $request, Post $post)
    {
        $this->ensureAuthor($request, $post);

        $post->load('media');

        return view('posts.edit', [
            'title' => 'Edit post',
            'post' => $post,
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->ensureAuthor($request, $post);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $post, $request) {
            $meta = $post->meta ?? [];
            if (!empty($validated['contest_status'])) {
                $meta['contest_status'] = $validated['contest_status'];
            }

            $post->fill([
                'type' => $validated['type'],
                'title' => $validated['title'] ?? null,
                'body' => $validated['body'] ?? null,
                'meta' => $meta ?: null,
                'author_display_name' => $validated['author_display_name'] ?? null,
                'author_contact' => $validated['author_contact'] ?? null,
                'status' => $request->session()->has('admin_id') ? Post::STATUS_APPROVED : Post::STATUS_PENDING,
                'published_at' => $request->session()->has('admin_id') ? now() : null,
            ])->save();

            if (!empty($validated['remove_media'])) {
                $post->media()
                    ->whereIn('id', $validated['remove_media'])
                    ->get()
                    ->each->delete();
            }

            $newMedia = array_filter($request->file('media', []));
            if (!empty($newMedia)) {
                $this->storeMedia($post, $newMedia);
            }

            if ($request->file('avatar') instanceof UploadedFile) {
                $file = $request->file('avatar');
                $avatarPath = $file->storeAs('posts/'.$post->uuid, 'avatar.'.$file->getClientOriginalExtension(), 'public');
                $post->meta = array_merge($post->meta ?? [], ['avatar_path' => $avatarPath]);
                $post->save();
            }
        });

        return redirect()->route('feed')->with('status', 'Пост обновлён и отправлен на модерацию.');
    }

    private function storeMedia(Post $post, array $files): void
    {
        if (empty($files)) {
            return;
        }

        $position = (int) $post->media()->max('position');

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $position++;
            $path = $file->storeAs(
                'posts/'.$post->uuid,
                now()->timestamp.'-'.$position.'.'.$file->getClientOriginalExtension(),
                'public'
            );

            $post->media()->create([
                'disk' => 'public',
                'path' => $path,
                'position' => $position,
            ]);
        }
    }

    public function destroy(Request $request, Post $post)
    {
        $this->ensureAuthor($request, $post);

        $post->delete();

        return redirect()->route('feed')->with('status', 'Пост удалён.');
    }
}
