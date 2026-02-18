#!/usr/bin/env php
<?php
declare(strict_types=1);

// Standalone translator: connects directly to PostgreSQL via DSN and updates posts.title/body
// Usage examples:
//   php scripts/translate_posts_dsn.php --host=80.249.147.84 --port=5432 --db=frendi_test --user=brand_lift_user --pass=secure_password --limit=5 --dry-run
//   LT_ENDPOINTS="https://translate.argosopentech.com/translate,https://libretranslate.com/translate" \
//   php scripts/translate_posts_dsn.php --host=80.249.147.84 --port=5432 --db=frendi_test --user=brand_lift_user --pass=secure_password --sleep=4000 --max-retries=10

// -------- CLI args --------
function arg(string $name, $default = null) {
    foreach ($GLOBALS['argv'] as $a) {
        if (preg_match('/^--'.preg_quote($name, '/').'=(.*)$/', $a, $m)) return $m[1];
        if ($a === '--'.$name) return true;
    }
    return $default;
}

$dbHost = (string) (arg('host') ?? getenv('DB_HOST') ?: '127.0.0.1');
$dbPort = (int)    (arg('port') ?? (getenv('DB_PORT') ?: 5432));
$dbName = (string) (arg('db')   ?? getenv('DB_NAME') ?: 'frendi_test');
$dbUser = (string) (arg('user') ?? getenv('DB_USER') ?: 'brand_lift_user');
$dbPass = (string) (arg('pass') ?? getenv('DB_PASS') ?: 'secure_password');

$limit     = (int) (arg('limit', 0)); // 0 = all
$batch     = (int) (arg('batch', 25));
$minId     = arg('min-id'); $minId = $minId === null ? null : (int)$minId;
$maxId     = arg('max-id'); $maxId = $maxId === null ? null : (int)$maxId;
$dryRun    = (bool) arg('dry-run', false);
$sleepMs   = (int) (arg('sleep', getenv('SLEEP_MS') ?: 2500));
$maxRetries= (int) (arg('max-retries', getenv('MAX_RETRIES') ?: 8));
$source    = (string) (arg('source', 'auto'));
$target    = (string) (arg('target', 'es'));

$endpoints = getenv('LT_ENDPOINTS');
if (!$endpoints) {
    // Use more stable public instances by default; the argos endpoint is flaky in DNS
    $endpoints = 'https://libretranslate.com/translate,https://translate.astian.org/translate';
}
$endpointList = array_values(array_filter(array_map('trim', explode(',', $endpoints))));
if (empty($endpointList)) {
    fwrite(STDERR, "[!] No LibreTranslate endpoints provided.\n");
    exit(2);
}

// -------- HTTP helpers --------
function http_post_json(string $url, array $payload, int $timeoutSec = 45): array {
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_TIMEOUT        => $timeoutSec,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 0;
        $err = curl_error($ch);
        curl_close($ch);
        if ($body === false) throw new RuntimeException('curl error: '.$err);
        return [$status, $body, []];
    }

    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => $json,
        'timeout' => $timeoutSec,
    ]]);
    $body = @file_get_contents($url, false, $ctx);
    $status = 0; $headers = [];
    if (isset($http_response_header) && is_array($http_response_header)) {
        $headers = $http_response_header;
        foreach ($http_response_header as $h) {
            if (preg_match('#^HTTP/\S+\s+(\d+)#', $h, $m)) { $status = (int)$m[1]; break; }
        }
    }
    if ($body === false) throw new RuntimeException('HTTP request failed');
    return [$status, $body, $headers];
}

function parse_retry_after(array $headers): int {
    foreach ($headers as $h) {
        if (stripos($h, 'Retry-After:') === 0) {
            $v = trim(substr($h, strlen('Retry-After:')));
            if (ctype_digit($v)) return (int)$v;
        }
    }
    return 0;
}

// rotate endpoints with backoff on 429/5xx/403
function translate_text_rotating(string $text, string $format, array $endpoints, string $source, string $target, int $sleepMs, int $maxRetries): string {
    if ($text === '') return '';
    $maxLen = 4000;
    $out = '';
    $len = mb_strlen($text);
    $idx = 0;
    for ($i = 0; $i < $len; $i += $maxLen) {
        $chunk = mb_substr($text, $i, $maxLen);
        $attempt = 0;
        while (true) {
            $url = $endpoints[$idx % count($endpoints)];
            $status = 0; $body = null; $headers = [];
            $exception = null;
            try {
                [$status, $body, $headers] = http_post_json($url, [
                    'q'      => $chunk,
                    'source' => $source,
                    'target' => $target,
                    'format' => $format,
                ]);
            } catch (Throwable $e) {
                // network/DNS error — treat like retryable and rotate
                $exception = $e;
            }

            if ($exception === null && $status >= 200 && $status < 300) {
                $data = json_decode((string)$body, true);
                if (!is_array($data) || !array_key_exists('translatedText', $data)) {
                    throw new RuntimeException('Invalid JSON from '.$url.': '.substr((string)$body, 0, 200));
                }
                $out .= (string)$data['translatedText'];
                if ($sleepMs > 0) usleep($sleepMs * 1000);
                break;
            }

            // handle rate limit/server errors or network exceptions
            $retryAfter = ($headers ? parse_retry_after($headers) : 0);
            $waitMs = max($sleepMs, $retryAfter > 0 ? $retryAfter * 1000 : 2500) * (1 + $attempt);
            usleep($waitMs * 1000);
            $attempt++;
            $idx++; // rotate endpoint
            if ($attempt <= $maxRetries) continue;

            if ($exception) {
                throw new RuntimeException('LibreTranslate network error from '.$url.': '.$exception->getMessage());
            }
            throw new RuntimeException('LibreTranslate HTTP '.$status.' from '.$url.': '.substr((string)$body, 0, 200));
        }
    }
    return $out;
}

// -------- Connect to DB --------
$oldLimit = ini_get('memory_limit');
@ini_set('memory_limit', '1024M');
@set_time_limit(0);
ob_implicit_flush(true);
if (function_exists('stream_set_write_buffer')) {
    @stream_set_write_buffer(STDOUT, 0);
    @stream_set_write_buffer(STDERR, 0);
}
$dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $dbHost, $dbPort, $dbName);
$pdo = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

fwrite(STDERR, "[i] Connected to {$dbHost}:{$dbPort}/{$dbName} as {$dbUser}\n");
fwrite(STDERR, "[i] Endpoints: ".implode(', ', $endpointList)."\n");

// -------- Iterate posts --------
$selectSql = 'SELECT id, title, body, author_display_name FROM posts WHERE 1=1';
$conds = [];
if ($minId !== null) { $conds[] = 'id >= :minId'; }
if ($maxId !== null) { $conds[] = 'id <= :maxId'; }
if ($conds) { $selectSql .= ' AND '.implode(' AND ', $conds); }
$selectSql .= ' ORDER BY id ASC LIMIT :limit OFFSET :offset';

$sel = $pdo->prepare($selectSql);
$upd = $pdo->prepare('UPDATE posts SET title = :title, body = :body, author_display_name = :name WHERE id = :id');

$offset = 0; $processed = 0; $previewed = 0; $totalLimit = $limit > 0 ? $limit : PHP_INT_MAX;
while ($processed + $previewed < $totalLimit) {
    $fetchLimit = min($batch, $totalLimit - $processed - $previewed);
    if ($fetchLimit <= 0) break;
    if ($minId !== null) $sel->bindValue(':minId', $minId, PDO::PARAM_INT);
    if ($maxId !== null) $sel->bindValue(':maxId', $maxId, PDO::PARAM_INT);
    $sel->bindValue(':limit', $fetchLimit, PDO::PARAM_INT);
    $sel->bindValue(':offset', $offset, PDO::PARAM_INT);
    $sel->execute();
    $rows = $sel->fetchAll();
    if (!$rows) break;

    fwrite(STDERR, "[i] Batch offset={$offset} size=".count($rows)."\n");
    foreach ($rows as $row) {
        $id = (int)$row['id'];
        $oldTitle = (string)($row['title'] ?? '');
        $oldBody  = (string)($row['body'] ?? '');
        $oldName  = (string)($row['author_display_name'] ?? '');
        try {
            fwrite(STDERR, "[*] Translating ID {$id}\n");
            $newTitle = translate_text_rotating($oldTitle, 'text', $endpointList, $source, $target, $sleepMs, $maxRetries);
            $newBody  = translate_text_rotating($oldBody, 'html', $endpointList, $source, $target, $sleepMs, $maxRetries);
            $newName  = $oldName !== '' ? translate_text_rotating($oldName, 'text', $endpointList, $source, $target, $sleepMs, $maxRetries) : $oldName;
        } catch (Throwable $e) {
            fwrite(STDERR, "[!] ID {$id} error: ".$e->getMessage()."\n");
            continue;
        }

        if ($dryRun) {
            $previewed++;
            fwrite(STDOUT, "ID {$id}\n- title: ".mb_substr($oldTitle,0,120)."\n+ title: ".mb_substr($newTitle,0,120)."\n");
            fwrite(STDOUT, "- name:  ".mb_substr($oldName,0,80)."\n+ name:  ".mb_substr($newName,0,80)."\n");
            $cleanOld = trim(strip_tags($oldBody));
            $cleanNew = trim(strip_tags($newBody));
            fwrite(STDOUT, "- body:  ".mb_substr($cleanOld,0,120)."\n+ body:  ".mb_substr($cleanNew,0,120)."\n\n");
            if ($previewed >= $totalLimit) break 2;
            continue;
        }

        $upd->execute([':title' => $newTitle, ':body' => $newBody, ':name' => $newName, ':id' => $id]);
        fwrite(STDERR, "[+] Updated ID {$id}\n");
        $processed++;
    }

    $offset += count($rows);
}

fwrite(STDERR, $dryRun
    ? "[✓] Previewed {$previewed} posts\n"
    : "[✓] Updated {$processed} posts\n");

