<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCacheVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем версию кэша только в режиме разработки
        if (app()->environment('local', 'development')) {
            $currentVersion = env('CACHE_VERSION', 1);
            $cacheFile = storage_path('app/cache_version.txt');
            
            $lastVersion = file_exists($cacheFile) ? file_get_contents($cacheFile) : null;
            
            if ($lastVersion !== (string)$currentVersion) {
                // Очищаем кэши в фоне
                \Artisan::call('config:clear');
                \Artisan::call('cache:clear');
                \Artisan::call('route:clear');
                \Artisan::call('view:clear');
                
                file_put_contents($cacheFile, $currentVersion);
            }
        }
        
        return $next($request);
    }
}
