<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Response;

class OgController extends Controller
{
    private const WIDTH = 1200;
    private const HEIGHT = 630;

    public function default(): Response
    {
        $img = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        $bg = imagecolorallocate($img, 16, 128, 129); // #108081
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, self::WIDTH, self::HEIGHT, $bg);
        // ASCII-only title to avoid encoding issues in GD built-in fonts
        imagestring($img, 5, 40, 40, 'Frendi', $white);

        // Also persist a static file so crawlers can fetch a stable asset path
        $target = public_path('images/og-default.jpg');
        @imagejpeg($img, $target, 90);

        ob_start(); imagejpeg($img, null, 90); $data = ob_get_clean();
        imagedestroy($img);

        return response($data, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function postBySlug(string $slug): Response
    {
        $post = Post::query()->where('share_slug', $slug)->first();

        $img = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        $bg = imagecolorallocate($img, 16, 128, 129); // #108081
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, self::WIDTH, self::HEIGHT, $bg);
        // Keep it simple to avoid charset artifacts
        imagestring($img, 5, 40, 40, 'Frendi', $white);

        ob_start();
        imagejpeg($img, null, 90);
        $data = ob_get_clean();
        imagedestroy($img);

        return response($data, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}


