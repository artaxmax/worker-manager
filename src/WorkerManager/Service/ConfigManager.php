<?php

namespace WorkerManager\Service;

use Noodlehaus\Config;

/**
 * WorkerManager\Service\ConfigManager
 */
class ConfigManager
{
    /**
     * @var array
     */
    static protected $configs = [];

    /**
     * @param string $path
     */
    static public function register($path)
    {
        if ((is_dir($path) || is_file($path)) && !in_array($path, static::$configs)) {
            static::$configs[] = $path;
        }
    }

    /**
     * @return \WorkerManager\Model\WorkerConfig[]
     * @throws \Exception
     */
    static public function loadWorkerConfig()
    {
        $config = static::getConfig();
        $className = $config->get('worker_manager.classes.worker');
        $queueClass = $config->get('worker_manager.classes.queue');
        $workerData = $config->get('worker_manager.worker') ?: [];
        $result = [];
        foreach ($workerData as $data) {
            /** @var \WorkerManager\Model\WorkerConfig $worker */
            /** @var \WorkerManager\Model\QueueConfig $queue */
            $worker = new $className($data['name']);
            $queue = new $queueClass($data['queue'], $data['vhost']);
            $worker->setQueue($queue)->setMinWorkerCount((int) $data['min_count'])
                ->setPriority((int) isset($data['priority']) ? $data['priority'] : 0);
            $result[] = $worker;
        }

        return $result;
    }
    /**
     * @return \WorkerManager\Model\RabbitMQVHostConfig[]
     * @throws \Exception
     */
    static public function loadRabbitMQConfig()
    {
        $config = static::getConfig();
        $className = $config->get('worker_manager.classes.rabbitmq');
        $configData = $config->get('worker_manager.rabbitmq') ?: [];
        $result = [];
        foreach ($configData as $data) {
            /** @var \WorkerManager\Model\RabbitMQVHostConfig $vhost */
            $vhost = new $className($data['name']);
            $vhost->setHost($data['host'])->setUsername($data['username'])
                ->setPassword($data['password']);
            if (isset($data['template'])) {
                $vhost->setHostTemplate($data['template']);
            }
            $result[] = $vhost;
        }

        return $result;
    }

    /**
     * @return \WorkerManager\Model\VMConfig[]
     * @throws \Exception
     */
    static public function loadVMConfig()
    {
        $config = static::getConfig();
        $className = $config->get('worker_manager.classes.supervisor');
        $workerData = $config->get('worker_manager.supervisor') ?: [];
        $result = [];
        foreach ($workerData as $data) {
            /** @var \WorkerManager\Model\VMConfig $supervisor */
            $supervisor = new $className($data['name']);
            $supervisor->setHost($data['host'])->setUsername($data['username'])
                ->setPassword($data['password'])->setMaxWorkerCount((int) $data['max_worker_count']);

            $result[] = $supervisor;
        }

        return $result;
    }

    /**
     * @return Config
     * @throws \Exception
     */
    static public function getConfig()
    {
        return Config::load(static::$configs);
    }
}
