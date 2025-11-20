<?php

namespace CodeInSync\Infrastructure\Composer;

class ComposerService
{
    protected $projectPath = '../';

    public function __construct($projectPath = null)
    {
        $this->projectPath = $projectPath ?? getcwd();
    }

    public function install($packageName = '')
    {
        $cmd = $packageName
            ? "composer require " . escapeshellarg($packageName)
            : "composer install";
        return $this->runCommand($cmd);
    }

    public function search($query)
    {
        $cmd = "composer search " . escapeshellarg($query) . " --format=json";
        $output = $this->runCommand($cmd);
        return json_decode($output, true) ?: ['output' => $output];
    }

    public function getInstalledPackages()
    {
        $output = $this->runCommand("composer show --format=json");
        return json_decode($output, true);
    }

    protected function runCommand($cmd)
    {
        $fullCmd = "cd " . escapeshellarg($this->projectPath) . " && $cmd 2>&1";
        return shell_exec($fullCmd);
    }
}
