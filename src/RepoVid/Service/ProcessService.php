<?php

namespace RepoVid\Service;

use Symfony\Component\Process\Process;

class ProcessService
{
    public function createProcess(
        $commandline, $cwd = null, array $env = null,
        $stdin = null, $timeout = 60, array $options = array()
    ) {
        return new Process($commandline, $cwd, $env, $stdin, $timeout, $options);
    }
}