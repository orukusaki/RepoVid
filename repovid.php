<?php
require_once __DIR__.'/vendor/autoload.php';

$app = new \Cilex\Application('RepoVid');
$app->register(new \Cilex\Provider\ConfigServiceProvider(), array('config.path' => __DIR__ . '/config.json'));
$app['process'] = new \RepoVid\Service\ProcessService();
$app['config.resolver'] = new \RepoVid\Service\ConfigResolverService();


$app->command(new \RepoVid\Command\GenerateCommand());


$app->run();