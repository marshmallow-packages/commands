<?php

namespace Marshmallow\Commands\Console\Commands;

use Illuminate\Console\Command;

class EnvironmentCommand extends Command
{
    private $key;

    private $value;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:set {key} {value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Voeg een variable toe aan je .env bestand.';

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
        $this->key = $this->argument('key');
        $this->value = $this->argument('value');

        $str = $this->getCurrentEnvFileContents();

        if ($this->envVariableDoesNotExists()) {
            /*
             * Bestaat nog niet, dus we voegen hem toe.
             */
            $this->add();
        } else {
            /*
             * Bestaat al, dus we updaten hem.
             */
            $this->update();
        }

        $this->info('.env bestand is gewijzigd.');
    }

    private function add()
    {
        $env_file_content = $this->getCurrentEnvFileContents();
        $env_file_content .= "\n";
        $env_file_content .= $this->key.'='.$this->convertToEnvironmentValue();
        $this->store($env_file_content);
    }

    private function update()
    {
        $matches = $this->findEnvVariable();
        $env_file_content = $this->getCurrentEnvFileContents();
        $env_file_content = str_replace(
            $matches[0],
            $this->key.'='.$this->convertToEnvironmentValue(),
            $env_file_content
        );
        $this->store($env_file_content);
    }

    private function envVariableDoesNotExists()
    {
        return (! $this->envVariableExists());
    }

    private function envVariableExists()
    {
        $matches = $this->findEnvVariable();

        return (! empty($matches));
    }

    private function findEnvVariable()
    {
        preg_match(
            "/{$this->key}=(.+)$/m",
            $this->getCurrentEnvFileContents(),
            $matches
        );

        return $matches;
    }

    private function getCurrentEnvFileContents()
    {
        return file_get_contents(
            $this->getEnvFilePath()
        );
    }

    private function getEnvFilePath()
    {
        return app()->environmentFilePath();
    }

    private function store($env_file_content)
    {
        $env_file = $this->getEnvFilePath();
        $fp = fopen($env_file, 'w');
        fwrite($fp, $env_file_content);
        fclose($fp);
    }

    private function convertToEnvironmentValue()
    {
        return (false === strpos($this->value, ' ')) ? $this->value : '"'.$this->value.'"';
    }
}
