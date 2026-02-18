<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckCacheVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:check-version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check CACHE_VERSION and clear caches if changed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentVersion = env('CACHE_VERSION', 1);
        $cacheFile = storage_path('app/cache_version.txt');
        
        $lastVersion = file_exists($cacheFile) ? file_get_contents($cacheFile) : null;
        
        if ($lastVersion !== (string)$currentVersion) {
            $this->info("Cache version changed from {$lastVersion} to {$currentVersion}. Clearing caches...");
            
            $this->call('config:clear');
            $this->call('cache:clear');
            $this->call('route:clear');
            $this->call('view:clear');
            $this->call('optimize:clear');
            
            file_put_contents($cacheFile, $currentVersion);
            
            $this->info('All caches cleared successfully!');
        } else {
            $this->info('Cache version unchanged, no action needed.');
        }
    }
}
