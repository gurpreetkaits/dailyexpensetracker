<?php

namespace App\Console\Commands;

use App\Mail\InactivityNudgeMail;
use App\Notifications\AskFeedbackNotification;
use App\Services\UserSegmentsService;
use http\Client\Curl\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class SendInactivityNudges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-inactivity-nudges';

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
        $days  = $this->ask('days');
        $email = $this->ask('email');
        if(!$email)  {
            $users = UserSegmentsService::inactive($days);
            $this->info("Sending nudges to {$users->count()} users…");
            foreach ($users as $u) {
                Notification::send($u, new AskFeedbackNotification());
            }
        }else{
            $u = \App\Models\User::where('email',$email)->first();
            Notification::send($u, new AskFeedbackNotification());
            $this->info('Mail sent to '. $u->email);
        }

    }
}
