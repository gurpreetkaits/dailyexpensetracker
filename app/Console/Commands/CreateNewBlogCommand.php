<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\NewBlogPostNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateNewBlogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'det:new-blog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new blog entry in the database';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $title = $this->ask('What is the title of the blog?');
        $url = $this->ask('What is the URL of the blog?');

        DB::table("blogs")->insert([
            'title' => $title,
            'url' => $url,
            'published_at' => now(),
        ]);

        $this->info('Done! New blog created successfully.');

        $users = User::chunk(500, function ($users) use ($title, $url) {
            foreach ($users as $user) {
                $user->notify(new NewBlogPostNotification($title, $url));
            }
        });
    }
}
