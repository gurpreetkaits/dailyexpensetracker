<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class WeeklyReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'det:weekly-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send users weekly summary emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::chunk(500, function ($users) {

            foreach ($users as $user) {
                $manager = app(\App\Managers\TransactionManager::class);
                $summary = $manager->getWeeklySummary($user->id);
                if ($user->email) {
                    $user->notify(new \App\Notifications\EmailWeeklySummaryNotificaton($summary));
                }
            }
        });
    }
}
