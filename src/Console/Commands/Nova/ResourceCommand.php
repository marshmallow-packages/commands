<?php

/**
 * Command to create Nova resources.
 *
 * PHP version 7.4
 *
 * @category Commands
 *
 * @author   Stef van Esch <stef@marshmallow.dev>
 * @license  MIT Licence
 *
 * @see     https://marshmallow.dev
 */

namespace Marshmallow\Commands\Console\Commands\Nova;

use Illuminate\Console\Command;
use Marshmallow\Commands\Traits\Stubs;

/**
 * Command to create Nova resource.
 *
 * @category Commands
 *
 * @author   Stef van Esch <stef@marshmallow.dev>
 * @license  MIT Licence
 *
 * @see     https://marshmallow.dev
 */
class ResourceCommand extends Command
{
    use Stubs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marshmallow:resource {resource_name?} {package_name?} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Nova resource from our own stub';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! $resource_name = $this->argument('resource_name')) {
            $resource_name = $this->ask('What is the Resource name?');
        }
        if (! $package_name = $this->argument('package_name')) {
            $package_name = $this->ask('What is the Package name?');
        }

        if (!$this->option('force') && file_exists($this->getStorePath())) {
            $file_exists_message = 'This resource already exists. '.
                                   'Are you sure you wish to continue? '.
                                   'This will override any changes you have made '.
                                   'to the existing resource file.';

            if ($this->confirm($file_exists_message)) {
                $this->storeFile();
            }
        } else {
            $this->storeFile();
        }
    }

    /**
     * Execute storeFile.
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
     * Execute getStorePath.
     *
     * @return mixed
     */
    protected function getStorePath()
    {
        return app_path().'/Nova/'.$this->argument('resource_name').'.php';
    }
}
