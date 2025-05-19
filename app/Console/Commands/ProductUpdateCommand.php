<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ProductUpdateNotification;
use Illuminate\Console\Command;

class ProductUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:product-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Product Update Email to all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->notify(new ProductUpdateNotification());
        }
    }
}
