<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ClearOldNotifications extends Command
{
    protected $signature = 'notifications:clear-old';
    protected $description = 'Delete notifications older than 3 days';

    public function handle(): int
    {
        $deleted = NotificationLog::where('created_at', '<', now()->subDays(3))->delete();

        Log::info('Cleared old notifications', ['count' => $deleted]);
        $this->info("Cleared {$deleted} old notifications.");

        return Command::SUCCESS;
    }
}
