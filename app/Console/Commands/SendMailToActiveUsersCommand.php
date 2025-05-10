<?php

namespace App\Console\Commands;

use App\Notifications\AskFeedbackNotification;
use App\Services\UserSegmentsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendMailToActiveUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mail-to-active-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = UserSegmentsService::active();
        $this->info("Sending mail to {$users->count()} active users…");
        foreach ($users as $user) {
            Notification::send($user, new AskFeedbackNotification());
        }
    }
}
