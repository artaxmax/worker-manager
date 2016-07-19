<?php
if (preg_match('/\.css|\.js|\.jpg|\.png|\.map$/', $_SERVER['REQUEST_URI'], $match)) {
    $mimeTypes = [
        '.css' => 'text/css',
        '.js'  => 'application/javascript',
        '.jpg' => 'image/jpg',
        '.png' => 'image/png',
        '.map' => 'application/json'
    ];
    $dir = dirname(__DIR__).'/src/WorkerManager/Resources/public/';
    $path = $dir . $_SERVER['REQUEST_URI'];
    if (is_file($path)) {
        header("Content-Type: {$mimeTypes[$match[0]]}");
        require $path;
        exit;
    }
}
require_once __DIR__.'/../vendor/autoload.php';

use WorkerManager\Service\ConfigManager;

ConfigManager::register(__DIR__.'/config');

$app = new \WorkerManager\Application();
$app->addComponent(new \WorkerManager\Service\Component\Authenticate());
$app->addService(
    'status_manager',
    \WorkerManager\Service\WorkerStatusManager::class
)->addService(
    'monitoring_helper',
    \WorkerManager\Helper\MonitoringHelper::class,
    [$app->getService('status_manager')]
);

$routing = new \WorkerManager\Service\Component\Routing();
$routing->add('/', 'index.html.twig', $app->getService('monitoring_helper'));
$app->addComponent($routing);

$app->run();