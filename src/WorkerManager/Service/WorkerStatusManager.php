<?php

namespace WorkerManager\Service;

use WorkerManager\Model\RabbitMQVHostConfig;
use WorkerManager\Model\VMConfig;
use WorkerManager\Model\WorkerConfig;
use WorkerManager\Model\WorkerProcess;

/**
 * WorkerManager\Service\WorkerStatusManager
 */
class WorkerStatusManager
{
    const OPTION_STATS_AGE = 'stats_age';
    const OPTION_STATS_INCR = 'stats_incr';

    /**
     * @var SupervisorManager
     */
    protected $supervisorMonitoring;

    /**
     * @var WorkerConfig[]
     */
    protected $workerConfigs;

    /**
     * @var RabbitMQVHostConfig[]
     */
    protected $rabbitMQConfig;

    /**
     * @var VMConfig[]
     */
    protected $VMConfig;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param WorkerConfig[]        $workerConfigs
     * @param RabbitMQVHostConfig[] $rabbitMQConfig
     * @param VMConfig[]            $VMConfig
     */
    public function __construct(
        array $workerConfigs = null,
        array $rabbitMQConfig = null,
        array $VMConfig = null
    ) {
        $this->workerConfigs = $workerConfigs;
        $this->rabbitMQConfig = $rabbitMQConfig;
        $this->VMConfig = $VMConfig;
    }

    /**
     * @return array
     */
    public function initData()
    {
        $workers = $this->loadWorkersConfig();
        $VMConfigs = $this->loadVMConfig();
        $this->fillVHostInfo($workers, $this->loadRabbitMQConfig());

        return [$workers, $VMConfigs];
    }

    /**
     * @param WorkerConfig[]        $workers
     * @param VMConfig[]            $VMConfigs
     *
     * @return array
     * @throws \Exception
     */
    public function updateStatus($workers, $VMConfigs)
    {
        $this->resetVMConfig($VMConfigs)->resetWorkersConfig($workers);
        $this->fillQueueInfo($workers);
        $this->fillStatus($workers, $VMConfigs);

        return [$workers, $VMConfigs];
    }

    /**
     * @return WorkerConfig[]
     */
    public function getWorkerStatus()
    {
        $workers = $this->loadWorkersConfig();
        $rabbitMQConfigs = $this->loadRabbitMQConfig();
        $this->fillVHostInfo($workers, $rabbitMQConfigs);
        $this->fillQueueInfo($workers);
        $VMConfigs = $this->loadVMConfig();
        $this->fillStatus($workers, $VMConfigs);

        return [$workers, $VMConfigs, $rabbitMQConfigs];
    }

    /**
     * @param VMConfig $VMConfig
     *
     * @return array
     * @throws \Exception
     */
    protected function loadStatus(VMConfig $VMConfig)
    {
        $status = SupervisorManager::init($VMConfig)->getHostStatus();
        if (null === $status) {
            throw new \Exception(sprintf('Unable to load %s supervisor status', $VMConfig->getName()));
        }

        return $status;
    }

    /**
     * @return WorkerConfig[]
     */
    protected function loadWorkersConfig()
    {
        if (null === $this->workerConfigs) {
            $this->workerConfigs = ConfigManager::loadWorkerConfig();
        }

        return $this->workerConfigs;
    }

    /**
     * @return array
     */
    protected function loadRabbitMQConfig()
    {
        if (null === $this->rabbitMQConfig) {
            $this->rabbitMQConfig = ConfigManager::loadRabbitMQConfig();
        }
        $result = [];
        foreach ($this->rabbitMQConfig as $config) {
            $result[$config->getName()] = $config;
        }

        return $result;
    }

    /**
     * @return VMConfig[]
     */
    protected function loadVMConfig()
    {
        if (null === $this->VMConfig) {
            $this->VMConfig = ConfigManager::loadVMConfig();
        }
        $result = [];
        foreach ($this->VMConfig as $config) {
            $result[$config->getName()] = $config;
        }

        return $result;
    }

    /**
     * Setter of Options
     *
     * @param array $options
     *
     * @return static
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge(
            [
                self::OPTION_STATS_AGE => 60,
                self::OPTION_STATS_INCR => 10,
            ],
            $options
        );

        return $this;
    }

    /**
     * @param WorkerConfig[] $workers
     * @param VMConfig[]     $VMConfigs
     *
     * @throws \Exception
     */
    protected function fillStatus($workers, $VMConfigs)
    {
        foreach ($VMConfigs as $host => $VMConfig) {
            $hostWorkers = $this->loadStatus($VMConfig);
            $VMConfig = $VMConfigs[$host];
            foreach ($hostWorkers as $workerData) {
                $search = preg_match('~^([^\:]+)\:(.+)_([0-9]{2})$~', $workerData['name'], $match);
                if ($search) {
                    /** @var WorkerConfig $worker */
                    foreach ($workers as $worker) {
                        if ($worker->getName() === $match[2]) {
                            $workerProcess = new WorkerProcess(
                                $workerData['name'],
                                $workerData['status'],
                                $workerData['pid'],
                                $workerData['uptime']
                            );
                            $worker->addProcess($workerProcess->setVMConfig($VMConfig))
                                ->setMaxWorkerCount(count($worker->getProcess()));
                            break;
                        }
                    }
                }
            }
        }
        foreach ($workers as $worker) {
            if (null === $worker->getProcess()) {
                throw new \Exception(
                    sprintf('Worker %s don\'t have any process', $worker->getName())
                );
            }
        }
    }

    /**
     * @param WorkerConfig[] $workers
     *
     * @throws \Exception
     */
    protected function fillQueueInfo($workers)
    {
        $grouped = $this->groupByVHost($workers);
        $messages = [];
        /** @var WorkerConfig $worker */
        foreach ($workers as $worker) {
            $queue = $worker->getQueue();
            $vHost = $queue->getVhostName();
            if (!isset($messages[$vHost])) {
                $workerNames = $grouped[$vHost];
                $messages[$vHost] = $this->getRabbitMQApi($queue->getVhost())->getVHostQueues(
                    $vHost,
                    function (array $queue) use ($workerNames) {
                        return in_array($queue['name'], $workerNames);
                    },
                    function (array $queue) {
                        $publishRate = $ackRate = $messageRate = null;
                        $stats = [];
                        if (isset($queue['message_stats']['publish_details'])) {
                            $publishRate = $queue['message_stats']['publish_details']['rate'];
                        }
                        if (isset($queue['message_stats']['ack_details'])) {
                            $ackRate = $queue['message_stats']['ack_details']['rate'];
                        }
                        if (isset($queue['messages_details']['avg_rate'])) {
                            $messageRate = $queue['messages_details']['avg_rate'];
                        }
                        if (isset($queue['messages_details']['samples'])) {
                            $stats = [];
                            foreach ($queue['messages_details']['samples'] as $sample) {
                                $stats[] = $sample['sample'];
                            }
                        }

                        return [
                            'count' => $queue['messages'],
                            'publishRate' => $publishRate,
                            'ackRate' => $ackRate,
                            'messageRate' => $messageRate,
                            'stats' => $stats,
                        ];
                    },
                    [
                        'lengths_age' => $this->options[self::OPTION_STATS_AGE],
                        'lengths_incr' => $this->options[self::OPTION_STATS_INCR]
                    ]
                );
            }
            $queueName = $queue->getName();
            if (!isset($messages[$vHost][$queueName])) {
                throw new \Exception(
                    sprintf('Unable to map %s queue', $queueName)
                );
            }
            $info = $messages[$vHost][$queueName];
            $queue->setMessages($info['count'])->setPublishRate($info['publishRate'])
                ->setAckRate($info['ackRate'])->setMessageRate($info['messageRate'])
                ->setStats($info['stats']);
        }
    }

    /**
     * @param WorkerConfig[] $workers
     *
     * @return array
     */
    protected function groupByVHost($workers)
    {
        $result = [];
        foreach ($workers as $worker) {
            $queue = $worker->getQueue();
            $vHost = $queue->getVhostName();
            if (!isset($result[$vHost])) {
                $result[$vHost] = [];
            }
            $result[$vHost][] = $queue->getName();
        }

        return $result;
    }

    /**
     * @param WorkerConfig[]        $workers
     * @param RabbitMQVHostConfig[] $vHosts
     *
     * @throws \Exception
     */
    protected function fillVHostInfo($workers, array $vHosts)
    {
        foreach ($workers as $worker) {
            $queue = $worker->getQueue();
            $vHost = $queue->getVhostName();
            if (isset($vHosts[$vHost])) {
                $queue->setVhost($vHosts[$vHost]);
            } else {
                throw new \Exception(
                    sprintf('Unexpected %s queue vhost - %s', $queue->getName(), $vHost)
                );
            }
        }
    }

    /**
     * @param RabbitMQVHostConfig $vhost
     *
     * @return RabbitmqApi
     * @throws \Exception
     */
    protected function getRabbitMQApi(RabbitMQVHostConfig $vhost)
    {
        $api = new RabbitmqApi(
            $vhost->getHost(),
            $vhost->getPort(),
            $vhost->getUsername(),
            $vhost->getPassword(),
            $vhost->getHostTemplate()
        );
        $api->addVhost($vhost->getName());

        return $api;
    }

    /**
     * @param VMConfig[] $VMConfig
     *
     * @return static
     */
    protected function resetVMConfig($VMConfig)
    {
        foreach ($VMConfig as $config) {
            $config->setProcesses([]);
        }

        return $this;
    }

    /**
     * @param WorkerConfig[]        $workers
     *
     * @return static
     */
    protected function resetWorkersConfig($workers)
    {
        foreach ($workers as $worker) {
            $worker->setProcess([])->setMaxWorkerCount(0)->setRunningCount(0)
                ->getQueue()->setMessages(null)->setPublishRate(null)->setAckRate(null)
                ->setMessageRate(null)->setStats([]);
        }

        return $this;
    }
}
