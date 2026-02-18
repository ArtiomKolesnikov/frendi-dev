<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class MassPetPostsSeeder extends Seeder
{
    private string $seedAuthorToken = 'seed-mass-pets';

    public function run(): void
    {
        $this->ensureSeedImages();

        $total = 100;
        $batchSize = 100;
        $images = $this->getSeedImagePaths();

        for ($offset = 0; $offset < $total; $offset += $batchSize) {
            $count = min($batchSize, $total - $offset);

            $now = Carbon::now();
            $names = $this->namesRu();
            $titles = $this->titlesRu();
            $bodies = $this->bodiesRu();

            $posts = [];
            $uuids = [];
            for ($i = 0; $i < $count; $i++) {
                $uuid = (string) Str::uuid();
                $uuids[] = $uuid;
                $title = Arr::random($titles);
                $status = (mt_rand(1, 100) <= 85) ? 'approved' : 'pending';
                $avatar = $this->randomAvatar();
                $meta = ['tags' => ['животные', 'питомцы'], 'avatar_path' => $avatar];
                $posts[] = [
                    'uuid' => $uuid,
                    'type' => 'pet',
                    'status' => $status,
                    'title' => $title,
                    'body' => Arr::random($bodies),
                    'meta' => json_encode($meta),
                    'author_display_name' => Arr::random($names),
                    'author_contact' => null,
                    'author_token' => $this->seedAuthorToken,
                    'is_admin' => false,
                    'share_slug' => Str::slug(Str::limit($title, 40, '')) . '-' . Str::lower(Str::random(6)),
                    'published_at' => $status === 'approved' ? $now->copy()->subDays(mt_rand(0, 90)) : null,
                    'created_at' => $now->copy()->subDays(mt_rand(0, 120))->subMinutes(mt_rand(0, 1440)),
                    'updated_at' => $now,
                ];
            }

            DB::table('posts')->insert($posts);

            // Map inserted ids by uuid
            $inserted = DB::table('posts')->whereIn('uuid', $uuids)->pluck('id', 'uuid');

            $mediaRows = [];
            $position = 0;
            $pathsCount = count($images);
            foreach ($uuids as $uuid) {
                $postId = $inserted[$uuid] ?? null;
                if (!$postId) { continue; }
                for ($j = 0; $j < 6; $j++) {
                    $path = $images[($position + $j) % $pathsCount];
                    $mediaRows[] = [
                        'post_id' => $postId,
                        'disk' => 'public',
                        'path' => $path,
                        'caption' => null,
                        'position' => $j,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                $position++;
            }

            foreach (array_chunk($mediaRows, 3000) as $chunk) {
                DB::table('post_media')->insert($chunk);
            }
        }
    }

    private function ensureSeedImages(): void
    {
        $disk = 'public';
        $dir = 'seed/animals';
        $fullDir = Storage::disk($disk)->path($dir);
        if (!is_dir($fullDir)) {
            @mkdir($fullDir, 0775, true);
        }

        $sourcesHd = [
            'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=1200&h=800&q=85',
            'https://images.unsplash.com/photo-1574158622682-e40e69881006?auto=format&fit=crop&w=1200&h=800&q=85',
            'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=1200&h=800&q=85',
            'https://images.unsplash.com/photo-1518791841217-8f1621e1131?auto=format&fit=crop&w=1200&h=800&q=85',
            'https://images.unsplash.com/photo-1507149833265-60c372daea22?auto=format&fit=crop&w=1200&h=800&q=85',
            'https://images.unsplash.com/photo-1543852786-1cf6624b9987?auto=format&fit=crop&w=1200&h=800&q=85',
            'https://images.unsplash.com/photo-1501706362039-c6e80948a90f?auto=format&fit=crop&w=1200&h=800&q=85',
            'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1200&h=800&q=85',
            'https://images.unsplash.com/photo-1508672019048-805c876b67e2?auto=format&fit=crop&w=1200&h=800&q=85',
            'https://images.unsplash.com/photo-1543852787-1cf6624b9987?auto=format&fit=crop&w=1200&h=800&q=85',
            'https://images.unsplash.com/photo-1568572933382-74d440642117?auto=format&fit=crop&w=1200&h=800&q=85',
            'https://images.unsplash.com/photo-1573663552611-0d3b1eaa50d8?auto=format&fit=crop&w=1200&h=800&q=85',
        ];

        $sourcesFallback = [
            'https://placekitten.com/800/600',
            'https://placekitten.com/801/600',
            'https://placekitten.com/802/600',
            'https://placekitten.com/803/600',
            'https://placekitten.com/804/600',
            'https://placekitten.com/805/600',
            'https://place.dog/600/400',
            'https://place.dog/601/400',
            'https://place.dog/602/400',
            'https://place.dog/603/400',
            'https://placebear.com/800/600',
            'https://placebear.com/801/600',
        ];

        $ua = 'Mozilla/5.0 (SeedBot)';
        $minSize = 30 * 1024;

        $i = 1;
        foreach ($sourcesHd as $url) {
            $rel = $dir.'/img_'.$i.'.jpg';
            $abs = Storage::disk($disk)->path($rel);
            $need = !file_exists($abs) || filesize($abs) < $minSize;
            if ($need) {
                $ok = $this->tryDownload($url, $rel, $ua);
                if (!$ok) {
                    $fallbackUrl = $sourcesFallback[($i - 1) % count($sourcesFallback)];
                    $ok = $this->tryDownload($fallbackUrl, $rel, $ua);
                }
                if (!$ok) {
                    $im = imagecreatetruecolor(1200, 800);
                    $bg = imagecolorallocate($im, 240, 240, 240);
                    imagefilledrectangle($im, 0, 0, 1200, 800, $bg);
                    $text = imagecolorallocate($im, 80, 80, 80);
                    imagestring($im, 5, 20, 20, 'Animal photo', $text);
                    ob_start(); imagejpeg($im, null, 85); $jpg = ob_get_clean(); imagedestroy($im);
                    Storage::disk($disk)->put($rel, $jpg);
                }
            }
            $i++;
        }

        $aDir = 'seed/avatars';
        $aFull = Storage::disk($disk)->path($aDir);
        if (!is_dir($aFull)) { @mkdir($aFull, 0775, true); }
        $aSrc = [
            'https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&fit=crop&w=400&h=400&q=80',
            'https://images.unsplash.com/photo-1504208434309-cb69f4fe52b0?auto=format&fit=crop&w=400&h=400&q=80',
            'https://images.unsplash.com/photo-1508672019048-805c876b67e2?auto=format&fit=crop&w=400&h=400&q=80',
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&h=400&q=80',
        ];
        for ($k=1;$k<=8;$k++){
            $rel = $aDir.'/a_'.$k.'.jpg';
            if (!Storage::disk($disk)->exists($rel)){
                $this->tryDownload($aSrc[$k % count($aSrc)], $rel, $ua);
            }
        }
    }

    private function getSeedImagePaths(): array
    {
        $disk = 'public';
        $dir = 'seed/animals';
        $files = [];
        for ($i = 1; $i <= 12; $i++) {
            $rel = $dir.'/img_'.$i.'.jpg';
            if (Storage::disk($disk)->exists($rel)) {
                $files[] = $rel;
            }
        }
        return $files ?: [$dir.'/img_1.jpg'];
    }

    private function randomAvatar(): string
    {
        $disk = 'public';
        $dir = 'seed/avatars';
        $i = mt_rand(1, 8);
        $rel = $dir.'/a_'.$i.'.jpg';
        if (Storage::disk($disk)->exists($rel)) return $rel;
        return 'images/avatar-placeholder.svg';
    }

    private function namesRu(): array
    {
        return [
            'Барсик','Мурзик','Снежок','Рыжик','Шарик','Дружок','Лаки','Чарли','Боня','Тайсон',
            'Сима','Луна','Даша','Бэлла','Ася','Грета','Алтай','Рекс','Бакс','Филя',
        ];
    }

    private function titlesRu(): array
    {
        return [
            'Мой пёс на прогулке', 'Кот исследует новый дом', 'Сонный котёнок после игры',
            'Утренние пробежки с собакой', 'Первое купание щенка', 'Лапы и хвосты: семейный выходной',
            'Кот в коробке — классика', 'Встреча с ветеринаром', 'Лучший друг человека',
            'Любовь к косточкам', 'Охота на красный луч', 'Новые игрушки для кота',
            'Снег и следы лап', 'Знакомство с соседским псом', 'Мягкие уши и тёплый нос',
        ];
    }

    private function bodiesRu(): array
    {
        return [
            'Сегодня наш пушистый друг покорил парк и нашёл десятки новых запахов.',
            'Кот уверенно проверил все комнаты и выбрал себе любимое место у окна.',
            'После активной игры пришло время сладко поспать на подоконнике.',
            'Мы начали утро с пробежки — энергии хватило на весь день!',
            'Первое купание прошло лучше, чем ожидали: немного волнений и много смеха.',
            'Семейный пикник удался: пес гонялся за мячом, а кот наблюдал из корзины.',
            'Коробка снова победила — ни одна игрушка не сравнится!',
            'Проверка здоровья прошла отлично, врач похвалил за дисциплину.',
            'Верность и радость в каждом взгляде — вот за что мы их любим.',
            'Лучшее лакомство — та самая косточка, найденная в заначке.',
            'Лазерная указка снова оказалась сильнее кошачьей гордости.',
            'Звук новых игрушек никого не оставил равнодушным — особенно соседей.',
            'Первый снег этой осенью вызвал настоящий восторг.',
            'Дружелюбная встреча на площадке закончилась совместной игрой.',
            'Тёплые уши и мокрый нос — идеальное утро.',
        ];
    }

    private function tryDownload(string $url, string $rel, string $ua): bool
    {
        try {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 20,
                    'header' => "User-Agent: $ua\r\nAccept: */*\r\n",
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);
            $data = @file_get_contents($url, false, $ctx);
            if ($data === false) {
                return false;
            }
            Storage::disk('public')->put($rel, $data);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}


