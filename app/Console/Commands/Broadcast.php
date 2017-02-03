<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Broadcast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadcast {game}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending Broadcast Message';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Fire off an event, just randomly grabbing the first user for now
        event(new App\Events\BroadcastForMatches($game));
    }
}
