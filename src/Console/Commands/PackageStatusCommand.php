<?php

namespace Marshmallow\Commands\Console\Commands;

use Illuminate\Console\Command;

/**
 * Check the git status by running 'php artisan package:status'
 * Check that --cs-fixer doesnt exist 'php artisan package:status --cs-fixer'
 * Check that a workflow exist 'php artisan package:status --has-workflow=php-syntax-checker'
 */

class PackageStatusCommand extends Command
{
    protected $result_table = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:status
                                {--cs-fixer}
                                {--has-workflow=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List a table of packages that have git changes.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $composer_file = base_path('composer.json');
        $composer_json = json_decode(file_get_contents($composer_file));

        foreach ($composer_json->repositories as $repo) {

            if (! isset($repo->symlink) || ! $repo->symlink) {
                continue;
            }

            $continue = $this->checkCsFixerIsRemoved($repo);

            if ($continue) {
                $continue = $this->checkItHasWorkflow($repo, 'php-syntax-checker');
            }

            if ($continue) {
                $this->getGitStatus($repo);
            }
        }

        $this->table(
            ['Package', 'Description', 'Version'],
            $this->result_table
        );

        $this->newLine();
        $this->info(count($this->result_table) . ' packages in the list above');
    }

    protected function getGitStatus($repo)
    {
        chdir($this->getBasePath($repo));
        $output = shell_exec('git status');
        $status = $this->getGitStatusFromOutput($output, $repo);
        if ($status !== true) {
            $this->addRepoToResultTable($repo, $status);
        }
    }

    protected function addRepoToResultTable($repo, $description)
    {
         $this->result_table[] = [
            $this->getRepoName($repo),
            $description,
            $this->getRepoVersion($repo),
        ];
    }

    protected function checkWorkflowExists($repo, $workflow)
    {
        $path = $this->getBasePath($repo) . '/.github/workflows/'. $workflow .'.yml';
        return file_exists($path);
    }

    protected function checkItHasWorkflow($repo, $workflow)
    {
        $exists = $this->checkWorkflowExists($repo, $workflow);
        if (! $exists) {
            $this->addRepoToResultTable($repo, "$workflow does not exists");
            return false;
        }

        return true;
    }

    protected function checkCsFixerIsRemoved($repo)
    {
        $exists = $this->checkWorkflowExists($repo, 'php-cs-fixer');
        if ($exists) {
            $this->addRepoToResultTable($repo, 'php-cs-fixer should be removed');
            return false;
        }

        return true;
    }

    protected function getRepoName($repo)
    {
        $composer_file = $this->getBasePath($repo) . '/composer.json';
        if (! file_exists($composer_file)) {
            return 'Unknown';
        }

        $composer_json = json_decode(file_get_contents($composer_file));
        return $composer_json->name;
    }

    protected function getRepoVersion($repo)
    {
        chdir($this->getBasePath($repo));
        $output = shell_exec('git tag');
        if (! $output) {
            return 'n/a';
        }

        return collect(explode("\n", $output))->reject(function ($value) {
            return ! $value;
        })->last();
    }

    protected function getGitStatusFromOutput($output, $repo)
    {
        if (! $output) {
            return 'Not a git repo';
        }

        if (strpos($output, 'Untracked')) {
            return 'Untracked';
        } else if (strpos($output, 'Changes not staged')) {
            return 'Unstaged';
        } else if (strpos($output, 'Your branch is behind')) {
            return 'Branch should be pulled';
        } else if (strpos($output, 'Your branch is ahead')) {
            return 'Branch should be pushed';
        } else if (strpos($output, 'nothing to commit')) {
            return true;
        }
        dd($output, $repo);
    }

    protected function getBasePath($repo)
    {
        return base_path(
            str_replace('./', '', $repo->url)
        );
    }
}
