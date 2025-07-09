<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\NewFeatureNotification;
use App\Services\UserSegmentsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NewFeatureAnnouncementCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:feature-announcement';

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
        $feature = $this->ask('filename','blade file name');
        $subject = $this->ask('subject','New Feature Announcement');

        $users = User::all();
        foreach ($users as $user) {
            Notification::send($user, new NewFeatureNotification($subject,$feature));
        }
    }
}
