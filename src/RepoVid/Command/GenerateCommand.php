<?php
/**
 * GenerateCommand Class
 *
 * @package RepoVid
 * @author  Peter Smith <peter@orukusaki.co.uk>
 * @link    github.com/orukusaki/RepoVid
 */
namespace RepoVid\Command;

use Cilex\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * GenerateCommand Class
 *
 * @package RepoVid
 * @author  Peter Smith <peter@orukusaki.co.uk>
 * @link    github.com/orukusaki/RepoVid
 */
class GenerateCommand extends Command
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('video:generate')
            ->setDescription('Generate some videos')
            ->addOption('d', null, InputOption::VALUE_NONE, "Dry Run (don't really generate videos)");
    }

    /**
     * Execute
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $app = $this->getContainer();
        $config = $app['config'];

        foreach ($app['config']->repos as $repo) {

            $output->writeln('Processing ' . $repo->name);
            $this->processRepo($repo);
        }
    }

    /**
     * Process a Repo
     *
     * @param array $repo Repo
     *
     * @return void
     */
    protected function processRepo(\stdClass $repo)
    {
        $app = $this->getContainer();

        $config = $app['config'];
        $config->repo = $repo;
        $config = $app['config.resolver']->resolve($config);

        $generateVid = false;

        $repoPath = $config->paths->repos . \DIRECTORY_SEPARATOR .  $config->repo->name;

        if (!is_dir($repoPath)) {

            $remotePath = $config->paths->remoteUrl;
            $this->output->writeln('Repo not found on disk, cloning from ' . $remotePath);

            $process = $app['process']->createProcess('git clone ' . $remotePath, $config->paths->repos);
            if ($process->run() != 0) {
                $this->output->writeln('Clone Failed');
                return;
            }
            $generateVid = true;
        }

        if (!is_file($config->paths->video)) {
            $this->output->writeln('No existing video found at ' . $config->paths->video);
            $generateVid = true;
        }

        $process = $app['process']->createProcess('git checkout ' . $config->repo->branch, $repoPath);
        if ($process->run() != 0) {
            $process = $app['process']->createProcess(
                'git checkout -b ' . $config->repo->branch . ' '
                . 'origin/' . $config->repo->branch,
                $repoPath
            );
            if ($process->run() != 0) {
                $this->output->writeln('Failed to checkout branch ' . $config->repo->branch);
                return;
            }
        }

        $process = $app['process']->createProcess('git pull origin ' . $config->repo->branch, $repoPath);
        $process->run();

        if (strstr($process->getOutput(), 'Already up-to-date.') === false) {
            $this->output->writeln('New Changes Found.');
            $generateVid = true;
        }

        $process = $app['process']->createProcess('git log --reverse --format=%ai | head -1', $repoPath);
        $process->run();
        $repoStart = strtotime($process->getOutput());

        $process = $app['process']->createProcess('git log --format=%ai | head -1', $repoPath);
        $process->run();
        $repoEnd = strtotime($process->getOutput());

        $projectStart = strtotime($config->project->start);
        $startPosition = ($repoEnd - $repoStart) ? ($projectStart - $repoStart) / ($repoEnd - $repoStart) : 0;
        $startPosition = max($startPosition, 0);
        $startPosition = min($startPosition, 1);

        $config->repo->startPosition = $startPosition;
        $config = $app['config.resolver']->resolve($config);

        if ($generateVid) {
            $command = 'gource ' . implode(' ', $config->gourceArgs)
                     . ' | '
                     . 'avconv ' . implode(' ', $config->avconvArgs);
            $this->output->writeln($command);

            $process = $app['process']->createProcess($command, $repoPath);
            if (!$this->input->getOption('d')) {
                $process->run();
            }
        }
    }
}