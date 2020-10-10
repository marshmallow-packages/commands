<?php

namespace Marshmallow\Commands\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ClearCacheCommand extends Command
{
    protected $commands = [
        'cache:clear',
        'route:clear',
        'config:clear',
        'view:clear',
        'clear-compiled',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marshmallow:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all cache clear artisan commands from Laravel';

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
        foreach ($this->commands as $command) {
            Artisan::call($command);
            $this->info(Artisan::output());
        }
    }
}
