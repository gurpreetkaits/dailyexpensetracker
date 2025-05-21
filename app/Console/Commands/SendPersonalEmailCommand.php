<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\SendPersonalNotification;
use Illuminate\Console\Command;

class SendPersonalEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-personal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email to single user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->ask('User email');
        $message = $this->ask('Message');
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error('User not found');
            return;
        }
        $user->notify(new SendPersonalNotification($message));
        $this->info('Sending email to ' . $user->email);
        $this->info('Email sent');
    }
}
