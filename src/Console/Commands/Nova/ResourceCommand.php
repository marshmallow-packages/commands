<?php

namespace Marshmallow\Commands\Console\Commands\Nova;

use Illuminate\Console\Command;
use Marshmallow\Commands\Traits\Stubs;

class ResourceCommand extends Command
{
    use Stubs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marshmallow:resource {resource_name?} {package_name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Nova resource from our own stub';

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
        if (!$resource_name = $this->argument('resource_name')) {
            $resource_name = $this->ask('What is the Resource name?');
        }
        if (!$package_name = $this->argument('package_name')) {
            $package_name = $this->ask('What is the Package name?');
        }

        if (file_exists($this->getStorePath())) {
            if ($this->confirm('This resource already exists. Are you sure you wish to continue? This will override any changes you have made to the existing resource file.')) {
                $this->storeFile();
            }
        } else {
            $this->storeFile();
        }
    }

    /**
     * Execute storeFile
     *
     * @return mixed
     */
    protected function storeFile()
    {
        $stub_content = $this->getStub(
            'nova.resource',
            [
            'package_name' => $this->argument('package_name'),
            'resource_name' => $this->argument('resource_name'),
            ]
        );

        file_put_contents($this->getStorePath(), $stub_content);

        $this->info('Your resource file has been created in your app/Nova folder.');
    }

    /**
     * Execute getStorePath
     *
     * @return mixed
     */
    protected function getStorePath()
    {
        return app_path() . '/Nova/' . $this->argument('resource_name') . '.php';
    }
}
