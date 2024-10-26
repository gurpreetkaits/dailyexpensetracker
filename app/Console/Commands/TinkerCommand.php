<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TinkerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:use';

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
        $currency = asset('currency.json');
        dd($currency);
    }
}
