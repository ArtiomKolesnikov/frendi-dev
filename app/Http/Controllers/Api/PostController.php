<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostMedia;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(Post::TYPES)],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'meta' => ['nullable', 'array'],
            'meta.contest_status' => ['nullable', Rule::in(['new', 'past'])],
            'author_display_name' => ['nullable', 'string', 'max:120'],
            'author_contact' => ['nullable', 'string', 'max:255'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'image', 'max:5120'],
        ]);

        $authorToken = ClientContext::token($request);

        $post = DB::transaction(function () use ($validated, $authorToken) {
            $post = Post::create([
                'type' => $validated['type'],
                'title' => $validated['title'] ?? null,
                'body' => $validated['body'] ?? null,
                'meta' => $validated['meta'] ?? null,
                'author_display_name' => $validated['author_display_name'] ?? null,
                'author_contact' => $validated['author_contact'] ?? null,
                'author_token' => $authorToken,
                'status' => Post::STATUS_PENDING,
                'published_at' => null,
            ]);

            $this->storeMedia($post, $validated['media'] ?? []);

            return $post;
        });

        $post->load(['media']);

        return PostResource::make($post)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Post $post): JsonResponse
    {
        $authorToken = ClientContext::token($request);

        if ($post->status !== Post::STATUS_APPROVED && !hash_equals($post->author_token ?? '', $authorToken)) {
            abort(404);
        }

        $post->load([
            'media',
            'comments' => fn ($q) => $q->visibleFor($authorToken)->latest()->limit(20),
        ])->loadCount([
            'reactions as likes_count' => fn ($q) => $q->where('type', 'like'),
            'reactions as dislikes_count' => fn ($q) => $q->where('type', 'dislike'),
            'comments as comments_count' => function ($q) use ($authorToken) {
                $q->where(function ($inner) use ($authorToken) {
                    $inner->where('status', Comment::STATUS_APPROVED)
                        ->orWhere('author_token', $authorToken);
                });
            },
        ]);

        $post->setAttribute('user_reaction', $post->reactions()
            ->where('author_token', $authorToken)
            ->value('type'));

        return PostResource::make($post)->response();
    }

    public function update(Request $request, Post $post): JsonResponse
    {
        $this->authorizeAuthor($request, $post);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'meta' => ['nullable', 'array'],
            'meta.contest_status' => ['nullable', Rule::in(['new', 'past'])],
            'author_display_name' => ['nullable', 'string', 'max:120'],
            'author_contact' => ['nullable', 'string', 'max:255'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'image', 'max:5120'],
            'remove_media_ids' => ['nullable', 'array'],
            'remove_media_ids.*' => ['integer', 'exists:post_media,id'],
        ]);

        DB::transaction(function () use ($post, $validated) {
            $post->fill([
                'title' => $validated['title'] ?? $post->title,
                'body' => $validated['body'] ?? $post->body,
                'meta' => $validated['meta'] ?? $post->meta,
                'author_display_name' => $validated['author_display_name'] ?? $post->author_display_name,
                'author_contact' => $validated['author_contact'] ?? $post->author_contact,
                'status' => Post::STATUS_PENDING,
                'published_at' => null,
            ])->save();

            if (!empty($validated['remove_media_ids'])) {
                $mediaToDelete = PostMedia::query()
                    ->where('post_id', $post->id)
                    ->whereIn('id', $validated['remove_media_ids'])
                    ->get();

                foreach ($mediaToDelete as $media) {
                    Storage::disk($media->disk ?? 'public')->delete($media->path);
                    $media->delete();
                }
            }

            $this->storeMedia($post, $validated['media'] ?? []);
        });

        $post->refresh()->load(['media']);

        return PostResource::make($post)->response();
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        $this->authorizeAuthor($request, $post);

        $post->delete();

        return response()->json(['status' => 'deleted']);
    }

    private function authorizeAuthor(Request $request, Post $post): void
    {
        $authorToken = ClientContext::token($request);
        abort_unless(hash_equals($post->author_token ?? '', $authorToken), 403, 'Недостаточно прав для изменения поста.');
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
}
