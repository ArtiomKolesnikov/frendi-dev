<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Post;
use Symfony\Component\Process\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('posts:purge {--force}', function () {
    if (!$this->option('force') && !$this->confirm('Это безвозвратно удалит ВСЕ посты и связанные данные (медиа-файлы, реакции, комментарии, жалобы, шэры, победителей конкурса). Продолжить?')) {
        $this->warn('Отменено.');
        return 130;
    }

    $this->info('Удаляю данные из БД (без удаления таблиц)...');
    try {
        // Собираем id и uuid всех постов
        $postIds = collect();
        $postUuids = collect();
        if (DB::getSchemaBuilder()->hasTable('posts')) {
            DB::table('posts')->select('id','uuid')->orderBy('id')->chunk(1000, function ($rows) use (&$postIds, &$postUuids) {
                foreach ($rows as $r) { $postIds->push($r->id); if (!empty($r->uuid)) { $postUuids->push($r->uuid); } }
            });
        }

        if ($postIds->isNotEmpty()) {
            DB::beginTransaction();
            // child → parent
            $tablesByPostId = ['post_media','post_reactions','comments','complaints','share_events','contest_winners'];
            foreach ($tablesByPostId as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    // удаляем только строки, связанные с постами
                    DB::table($table)->whereIn('post_id', $postIds)->delete();
                }
            }
            // затем посты
            if (DB::getSchemaBuilder()->hasTable('posts')) {
                DB::table('posts')->whereIn('id', $postIds)->delete();
            }
            DB::commit();
        } else {
            $this->line('Постов не найдено — пропускаю очистку БД.');
        }
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);
        $this->error('Ошибка при удалении из БД: '.$e->getMessage());
        return 1;
    }

    $this->info('Очищаю файлы медиа...');
    try {
        // Аккуратно удаляем только каталоги конкретных постов
        if ($postUuids->isNotEmpty()) {
            foreach ($postUuids->unique() as $uuid) {
                Storage::disk('public')->deleteDirectory('posts/'.$uuid);
            }
        } else {
            // на всякий случай — если нужно полностью очистить, раскомментируйте
            // Storage::disk('public')->deleteDirectory('posts');
            // Storage::disk('public')->makeDirectory('posts');
        }
    } catch (\Throwable $e) {
        report($e);
        $this->error('Ошибка при очистке файлов: '.$e->getMessage());
        return 1;
    }

    $this->info('Готово: все посты и сопутствующие данные удалены.');
    return 0;
})->purpose('Удалить все посты и связанные данные (файлы/реакции/комментарии/жалобы/шэр-события/победители конкурса)');

/**
 * Удалить ВСЕ сгенерированные посты по маркеру (author_token), вместе со всеми зависимостями и файлами.
 * По умолчанию маркером является 'seed-mass-pets' (массовая генерация из миграции 2025_09_23_060000_seed_10k_pet_posts_with_media.php).
 * Пример:
 *   php artisan posts:purge-generated --force
 *   php artisan posts:purge-generated --token=seed-mass-pets --also-assets --force
 */
Artisan::command('posts:purge-generated {--token=seed-mass-pets : Значение author_token, помечающее сгенерированные посты} {--also-assets : Удалить каталоги storage/app/public/seed/* если больше не используются} {--force : Пропустить подтверждение}', function () {
    $token = (string)$this->option('token');
    $alsoAssets = (bool)$this->option('also-assets');

    if ($token === '') {
        $this->error('Нужно указать --token (author_token маркер).');
        return 2;
    }

    $count = DB::table('posts')->where('author_token', $token)->count();
    if ($count === 0) {
        $this->info("Постов с author_token='{$token}' не найдено.");
        return 0;
    }

    if (!$this->option('force')) {
        if (!$this->confirm("Удалить {$count} сгенерированных постов (token='{$token}') со всеми зависимостями и файлами?")) {
            $this->warn('Отменено.');
            return 130;
        }
    }

    // Create DB backup in project root before deletion
    try {
        $pgsql = config('database.connections.pgsql');
        if (!$pgsql) { throw new \RuntimeException('Не найдена конфигурация соединения pgsql.'); }

        $host = (string)($pgsql['host'] ?? '127.0.0.1');
        $port = (string)($pgsql['port'] ?? '5432');
        $db   = (string)($pgsql['database'] ?? '');
        $user = (string)($pgsql['username'] ?? '');
        $pass = (string)($pgsql['password'] ?? '');
        if ($db === '' || $user === '') {
            throw new \RuntimeException('Не заданы database/username для pgsql.');
        }

        $backupDir = base_path('db-backups');
        if (!is_dir($backupDir)) { @mkdir($backupDir, 0775, true); }
        $backupFile = $backupDir.'/db-backup-'.date('Ymd_His').'.dump';
        $this->info('Создаю бэкап БД перед удалением: '.str_replace(base_path().DIRECTORY_SEPARATOR, '', $backupFile));

        // Use custom format (-F c) with compression (-Z 9) to a single file
        $cmd = 'PGPASSWORD='.escapeshellarg($pass).' pg_dump -h '.escapeshellarg($host).' -p '.escapeshellarg($port).' -U '.escapeshellarg($user).' -d '.escapeshellarg($db).' -F c -Z 9 -f '.escapeshellarg($backupFile);
        $process = Process::fromShellCommandline($cmd, base_path(), null, null, 600);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Не удалось создать бэкап БД: '.$process->getErrorOutput());
            return 2;
        }

        if (!is_file($backupFile) || filesize($backupFile) <= 0) {
            $this->error('Бэкап БД не создан или пуст. Операция отменена.');
            return 2;
        }

        $this->info('Бэкап создан: '.realpath($backupFile));
    } catch (\Throwable $e) {
        report($e);
        $this->error('Ошибка бэкапа БД: '.$e->getMessage());
        return 2;
    }

    $this->info("Готовлю списки id/uuid для удаления ({$count})...");
    $ids = [];
    $uuids = [];
    DB::table('posts')->select('id','uuid')->where('author_token', $token)->orderBy('id')
        ->chunk(1000, function ($rows) use (&$ids, &$uuids) {
            foreach ($rows as $r) { $ids[] = $r->id; if (!empty($r->uuid)) { $uuids[] = $r->uuid; } }
        });

    if (empty($ids)) {
        $this->info('Нечего удалять.');
        return 0;
    }

    $tablesByPostId = ['post_media','post_reactions','comments','complaints','share_events','contest_winners'];

    $this->info('Удаляю связанные записи (реакции, комментарии, жалобы, шэр-события, медиа)...');
    DB::beginTransaction();
    try {
        foreach (array_chunk($ids, 1000) as $chunk) {
            foreach ($tablesByPostId as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->whereIn('post_id', $chunk)->delete();
                }
            }
            DB::table('posts')->whereIn('id', $chunk)->delete();
        }
        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);
        $this->error('Ошибка при удалении: '.$e->getMessage());
        return 1;
    }

    $this->info('Удаляю каталоги с медиа каждого поста...');
    foreach (array_unique(array_filter($uuids)) as $uuid) {
        try { Storage::disk('public')->deleteDirectory('posts/'.$uuid); } catch (\Throwable $e) { /* ignore */ }
    }

    if ($alsoAssets) {
        $this->info('Проверяю, используются ли seed-ассеты...');
        $leftSeedMedia = 0;
        if (DB::getSchemaBuilder()->hasTable('post_media')) {
            $leftSeedMedia = (int) DB::table('post_media')->where('path', 'like', 'seed/%')->count();
        }
        if ($leftSeedMedia === 0) {
            $this->info('Seed-ассеты больше не используются. Удаляю каталоги storage/app/public/seed/{animals,avatars}...');
            try { Storage::disk('public')->deleteDirectory('seed/animals'); } catch (\Throwable $e) { /* ignore */ }
            try { Storage::disk('public')->deleteDirectory('seed/avatars'); } catch (\Throwable $e) { /* ignore */ }
        } else {
            $this->line("Пропускаю удаление seed-ассетов: осталось ссылок в post_media: {$leftSeedMedia}");
        }
    }

    $this->info('Готово: сгенерированные посты удалены.');
    return 0;
})->purpose('Удалить все сгенерированные посты (по author_token) со связями и медиа');

/**
 * Перевести тексты постов на испанский и перезаписать.
 *
 * Примеры:
 *  php artisan posts:translate --dry-run --limit=5
 *  php artisan posts:translate --target=es --api-url=https://libretranslate.com/translate
 */
Artisan::command('posts:translate {--limit=0 : Ограничить кол-во постов} {--dry-run : Только показать превью без сохранения} {--source=auto : Исходный язык} {--target=es : Целевой язык} {--api-url=https://libretranslate.com/translate : URL LibreTranslate} {--sleep=300 : Базовая пауза между запросами, мс} {--max-retries=5 : Повторить при 429/5xx}', function () {
    $limit   = (int)$this->option('limit');
    $dryRun  = (bool)$this->option('dry-run');
    $source  = (string)$this->option('source');
    $target  = (string)$this->option('target');
    $apiUrl  = (string)$this->option('api-url');
    $sleepMs = (int)$this->option('sleep');
    $maxRetries = (int)$this->option('max-retries');

    $this->info("Перевод постов: {$source} -> {$target} через {$apiUrl}" . ($dryRun ? ' [DRY-RUN]' : ''));

    $translate = function (string $text, string $format) use ($apiUrl, $source, $target, $sleepMs, $maxRetries) {
        if ($text === '') { return ''; }
        $maxLen = 4000; // бережём публичный инстанс
        $result = '';
        $length = mb_strlen($text);
        for ($i = 0; $i < $length; $i += $maxLen) {
            $chunk = mb_substr($text, $i, $maxLen);
            $attempt = 0;
            while (true) {
                $resp = Http::timeout(45)
                    ->acceptJson()
                    ->asJson()
                    ->post($apiUrl, [
                        'q'      => $chunk,
                        'source' => $source,
                        'target' => $target,
                        'format' => $format, // 'text' | 'html'
                    ]);

                if ($resp->ok()) {
                    $data = $resp->json();
                    if (!is_array($data) || !array_key_exists('translatedText', $data)) {
                        throw new \RuntimeException('LibreTranslate invalid JSON: ' . Str::limit(json_encode($data), 200));
                    }
                    $result .= (string)$data['translatedText'];
                    if ($sleepMs > 0) { usleep($sleepMs * 1000); }
                    break;
                }

                $status = $resp->status();
                $body = Str::limit($resp->body() ?? '', 200);
                if (($status == 429 || $status >= 500) && $attempt < $maxRetries) {
                    // выставляем адаптивную задержку
                    $retryAfter = (int)($resp->header('Retry-After') ?? 0);
                    $waitMs = max($sleepMs, $retryAfter > 0 ? $retryAfter * 1000 : 2500) * (1 + $attempt);
                    usleep($waitMs * 1000);
                    $attempt++;
                    continue;
                }

                throw new \RuntimeException('LibreTranslate HTTP ' . $status . ': ' . $body);
            }
        }
        return $result;
    };

    $processed = 0;
    $previewed = 0;
    $query = Post::query()->orderBy('id');
    if ($limit > 0) { $query->limit($limit); }

    $command = $this; // передаём ссылку на команду во вложенную функцию
    $query->chunkById(25, function ($posts) use (&$processed, &$previewed, $translate, $dryRun, $command) {
        foreach ($posts as $post) {
            $oldTitle = (string)($post->title ?? '');
            $oldBody  = (string)($post->body ?? '');
            try {
                $newTitle = $translate($oldTitle, 'text');
                $newBody  = $translate($oldBody, 'html');
            } catch (\Throwable $e) {
                $command->error("Ошибка перевода ID {$post->id}: {$e->getMessage()}");
                continue;
            }

            if ($dryRun) {
                $previewed++;
                $command->line("ID {$post->id}\n- title: " . Str::limit($oldTitle, 120) . "\n+ title: " . Str::limit($newTitle, 120));
                $command->line("- body:  " . Str::limit(strip_tags($oldBody), 120));
                $command->line("+ body:  " . Str::limit(strip_tags($newBody), 120));
                $command->newLine();
                continue;
            }

            DB::table('posts')->where('id', $post->id)->update([
                'title' => $newTitle,
                'body'  => $newBody,
            ]);
            $processed++;
        }
    });

    if ($dryRun) {
        $this->info("Готово: предпросмотрено {$previewed} постов (без сохранения)");
    } else {
        $this->info("Готово: обновлено {$processed} постов");
    }
})->purpose('Перевод постов на испанский через LibreTranslate');

/**
 * Оставить только N последних постов, удалить остальные вместе со всеми связанными данными и файлами.
 * По умолчанию N=50. Удаляет строки из таблиц, связанных по post_id, и очищает каталоги storage/posts/{uuid}.
 */
Artisan::command('posts:trim {count=50} {--force : Пропустить подтверждение}', function () {
    $keep = (int)$this->argument('count');
    if ($keep < 0) { $keep = 0; }
    if (!$this->option('force') && !$this->confirm("Оставить только {$keep} последних постов и удалить остальные со всеми связями и файлами?")) {
        $this->warn('Отменено.');
        return 130;
    }

    $keepIds = DB::table('posts')->orderByDesc('id')->limit($keep)->pluck('id')->all();
    $total = DB::table('posts')->count();
    if ($total <= $keep) {
        $this->info("Постов {$total} <= {$keep}. Нечего удалять.");
        return 0;
    }

    $idsToDelete = DB::table('posts')->whereNotIn('id', $keepIds)->orderBy('id')->pluck('id')->all();
    if (empty($idsToDelete)) {
        $this->info('Нечего удалять.');
        return 0;
    }

    $uuidsToDelete = DB::table('posts')->whereIn('id', $idsToDelete)->pluck('uuid')->filter()->all();

    $this->info('К удалению постов: '.count($idsToDelete)." (оставляем {$keep})");

    DB::beginTransaction();
    try {
        $tablesByPostId = ['post_media','post_reactions','comments','complaints','share_events','contest_winners'];
        foreach (array_chunk($idsToDelete, 1000) as $chunk) {
            foreach ($tablesByPostId as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->whereIn('post_id', $chunk)->delete();
                }
            }
            DB::table('posts')->whereIn('id', $chunk)->delete();
        }
        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);
        $this->error('Ошибка при удалении: '.$e->getMessage());
        return 1;
    }

    // Чистим файлы вне транзакции
    $this->info('Очищаю каталоги медиа для удалённых постов...');
    foreach (array_unique(array_filter($uuidsToDelete)) as $uuid) {
        try { Storage::disk('public')->deleteDirectory('posts/'.$uuid); } catch (\Throwable $e) { /* ignore */ }
    }

    $this->info('Готово: посты обрезаны до '.$keep.'.');
    return 0;
})->purpose('Оставить только N последних постов (по id) с полной очисткой связей и файлов');

/**
 * Удалить один пост по ID со всеми зависимостями и файлами.
 */
Artisan::command('posts:delete {id} {--force : Пропустить подтверждение}', function () {
    $id = (int)$this->argument('id');
    if ($id <= 0) { $this->error('Некорректный id'); return 1; }
    if (!$this->option('force') && !$this->confirm("Удалить пост #{$id} и все связанные данные/файлы?")) {
        $this->warn('Отменено.');
        return 130;
    }

    $post = DB::table('posts')->select('id','uuid')->where('id',$id)->first();
    if (!$post) { $this->info('Пост не найден.'); return 0; }

    DB::beginTransaction();
    try {
        $tablesByPostId = ['post_media','post_reactions','comments','complaints','share_events','contest_winners'];
        foreach ($tablesByPostId as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->where('post_id', $id)->delete();
            }
        }
        DB::table('posts')->where('id', $id)->delete();
        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);
        $this->error('Ошибка удаления: '.$e->getMessage());
        return 1;
    }

    if (!empty($post->uuid)) {
        try { Storage::disk('public')->deleteDirectory('posts/'.$post->uuid); } catch (\Throwable $e) { /* ignore */ }
    }
    $this->info('Пост удалён: #'.$id);
    return 0;
})->purpose('Удалить один пост по ID со связями и файлами');

/**
 * Оставить только N последних постов, остальные удалить вместе с зависимостями и файлами.
 * Пример: php artisan posts:reduce --keep=50 --force
 */
Artisan::command('posts:reduce {--keep=50 : Сколько последних постов оставить} {--force : Пропустить подтверждение}', function () {
    $keep = max(0, (int)$this->option('keep'));
    if ($keep <= 0) {
        $this->error('Параметр --keep должен быть > 0');
        return 1;
    }

    if (!$this->option('force')) {
        if (!$this->confirm("Оставить только {$keep} последних постов и удалить остальные со всеми зависимостями?")) {
            $this->warn('Отменено.');
            return 130;
        }
    }

    $this->info("Определяю {$keep} последних постов...");
    $keepIds = DB::table('posts')->orderByDesc('id')->limit($keep)->pluck('id')->all();
    if (empty($keepIds)) {
        $this->info('Постов нет — ничего удалять.');
        return 0;
    }

    $this->info('Собираю посты к удалению...');
    $delete = DB::table('posts')->select('id','uuid')->whereNotIn('id', $keepIds)->get();
    if ($delete->isEmpty()) {
        $this->info('Удалять нечего — постов больше, чем лимит, не найдено.');
        return 0;
    }

    $deleteIds = $delete->pluck('id')->all();
    $deleteUuids = $delete->pluck('uuid')->filter()->unique()->all();

    $this->info('Удаляю связанные записи...');
    DB::beginTransaction();
    try {
        $tablesByPostId = ['post_media','post_reactions','comments','complaints','share_events','contest_winners'];
        foreach ($tablesByPostId as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->whereIn('post_id', $deleteIds)->delete();
            }
        }
        DB::table('posts')->whereIn('id', $deleteIds)->delete();
        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);
        $this->error('Ошибка при удалении из БД: '.$e->getMessage());
        return 2;
    }

    $this->info('Удаляю файлы медиа...');
    foreach ($deleteUuids as $uuid) {
        try { Storage::disk('public')->deleteDirectory('posts/'.$uuid); } catch (\Throwable $e) { /* ignore single failures */ }
    }

    $this->info('Готово: удалено постов: '.count($deleteIds).', оставлено: '.$keep);
    return 0;
})->purpose('Оставить только N последних постов, удалить остальные (со всеми файлами/зависимостями)');
