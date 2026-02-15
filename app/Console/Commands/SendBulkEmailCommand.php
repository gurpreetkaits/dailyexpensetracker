<?php

namespace App\Console\Commands;

use App\Mail\BulkEmail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendBulkEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-bulk-email 
                            {view : The view file to use (e.g., mail.opensource-announcement)}
                            {subject : The email subject}
                            {--offset=0 : Start from this user offset}
                            {--limit=50 : Number of users to send to}
                            {--all : Send to all users (ignores offset/limit)}
                            {--sync : Send synchronously instead of queuing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send bulk email to users using a view template (queued by default)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $view = $this->argument('view');
        $subject = $this->argument('subject');
        $offset = (int) $this->option('offset');
        $limit = (int) $this->option('limit');
        $sendAll = $this->option('all');
        $sync = $this->option('sync');

        // Check if view exists
        if (!view()->exists($view)) {
            $this->error("View '{$view}' not found!");
            return 1;
        }

        // Get users
        $query = User::whereNotNull('email')->where('email', '!=', '')->orderBy('id');
        
        if (!$sendAll) {
            $query->offset($offset)->limit($limit);
        }
        
        $users = $query->get();
        $total = $users->count();

        if ($total === 0) {
            $this->warn('No users found to send emails to.');
            return 0;
        }

        $mode = $sync ? 'synchronously' : 'via queue';
        $this->info("Sending '{$subject}' to {$total} users {$mode}...");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $queued = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                $mailable = new BulkEmail($view, $subject, $user);
                
                if ($sync) {
                    Mail::to($user->email)->send($mailable);
                } else {
                    Mail::to($user->email)->queue($mailable);
                }
                
                $queued++;
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("Failed: {$user->email} - {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $action = $sync ? 'Sent' : 'Queued';
        $this->info("Done! {$action}: {$queued}, Failed: {$failed}");
        
        if (!$sync) {
            $this->info("Run 'php artisan queue:work' to process the queue.");
        }
        
        if (!$sendAll) {
            $nextOffset = $offset + $limit;
            $this->info("Next batch: php artisan app:send-bulk-email \"{$view}\" \"{$subject}\" --offset={$nextOffset} --limit={$limit}");
        }

        return 0;
    }
}
