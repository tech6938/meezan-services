<?php

namespace App\Console\Commands;

use App\Models\Chat;
use Illuminate\Console\Command;

class DeleteChat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-chat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is for delete chats ';

    /**
     * Execute the console command.
     */
  public function handle()
{
    $query = Chat::whereHas('bookingRequestsForUser', function ($query) {
        $query->whereColumn('user_id', 'chats.sender_id')
              ->whereColumn('provider_id', 'chats.receiver_id')
              ->where('status', 'complete_booking');
    });

    if ($query->exists()) {
        $deletedCount = $query->delete(); // ✅ WORKS
        $this->info("{$deletedCount} chats deleted successfully.");
    } else {
        $this->info('There is no data.');
    }

    return Command::SUCCESS;
}

}











