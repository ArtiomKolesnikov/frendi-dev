<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PostMediaService
{
    /**
     * @param UploadedFile[] $files
     */
    public function store(Post $post, array $files): void
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

    /**
     * @param int[] $mediaIds
     */
    public function deleteByIds(Post $post, array $mediaIds): void
    {
        if (empty($mediaIds)) {
            return;
        }

        $mediaToDelete = PostMedia::query()
            ->where('post_id', $post->id)
            ->whereIn('id', $mediaIds)
            ->get();

        foreach ($mediaToDelete as $media) {
            Storage::disk($media->disk ?? 'public')->delete($media->path);
            $media->delete();
        }
    }
}

