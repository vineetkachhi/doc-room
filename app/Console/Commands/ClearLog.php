<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearLog extends Command
{
    // Artisan command signature
    protected $signature = 'log:clear'; // <-- यह जरूरी है

    // Command description
    protected $description = 'Clear Laravel log file';

    public function handle()
    {
        $logFile = storage_path('logs/laravel.log');

        if (file_exists($logFile)) {
            unlink($logFile);
            $this->info('Laravel log cleared successfully.');
        } else {
            $this->info('No log file found.');
        }
    }
}
