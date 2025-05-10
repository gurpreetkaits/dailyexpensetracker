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
        $feature = $this->ask('feature','test feature');
        $singleUser = $this->ask('single-user');
        if($singleUser){
            $user = User::where('email', $singleUser)->first();
            if($user){
                Notification::send($user, new NewFeatureNotification($feature));
            }else{
                $this->error('User not found');
            }
        }else{
            $users = UserSegmentsService::active();
            foreach ($users as $user) {
                Notification::send($user, new NewFeatureNotification($feature));
            }
        }
    }
}
