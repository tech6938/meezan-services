<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FcmTokenService;

class TestFcmNotification extends Command
{
    protected $signature = 'fcm:test {title?} {body?}';
    protected $description = 'Send a test FCM notification to all users';

    protected FcmTokenService $fcmService;

    public function __construct(FcmTokenService $fcmService)
    {
        parent::__construct();
        $this->fcmService = $fcmService;
    }

    public function handle()
    {
        $title = $this->argument('title') ?? 'Test Notification';
        $body = $this->argument('body') ?? 'This is a test notification from your Laravel app';
        
        $this->info('Sending test notification...');
        
        $result = $this->fcmService->sendToAllUsers($title, $body, ['test' => 'true']);
        
        if ($result['success']) {
            $this->info('Notification sent successfully!');
            if (isset($result['success_count'])) {
                $this->info("Sent to {$result['success_count']} devices");
            }
        } else {
            $this->error('Failed to send notification: ' . ($result['error'] ?? 'Unknown error'));
        }
    }
}