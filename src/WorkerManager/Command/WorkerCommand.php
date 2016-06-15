<?php

namespace WorkerManager\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WorkerManager\Interfaces\LoggerInterface;
use WorkerManager\Service\ActionFileManager;
use WorkerManager\Service\ConfigManager;
use WorkerManager\Service\WorkerStatusManager;
use WorkerManager\Service\WorkerUpdateManager;

/**
 * WorkerManager\Command\WorkerCommand
 */
class WorkerCommand extends AbstractWorkerCommand
{
    /**
     * @var WorkerStatusManager
     */
    protected $statusManager;

    /**
     * @var WorkerUpdateManager
     */
    protected $updateManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Getter of Logger
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Setter of Logger
     *
     * @param LoggerInterface $logger
     *
     * @return static
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Setter of UpdateManager
     *
     * @param WorkerUpdateManager $updateManager
     *
     * @return static
     */
    public function setUpdateManager(WorkerUpdateManager $updateManager)
    {
        $this->updateManager = $updateManager;

        return $this;
    }

    /**
     * Setter of StatusManager
     *
     * @param WorkerStatusManager $statusManager
     *
     * @return static
     */
    public function setStatusManager(WorkerStatusManager $statusManager)
    {
        $this->statusManager = $statusManager;

        return $this;
    }

    /**
     * Getter of StatusManager
     *
     * @return WorkerStatusManager
     */
    public function getStatusManager()
    {
        if (null === $this->statusManager) {
            $this->statusManager = new WorkerStatusManager();
        }

        return $this->statusManager;
    }

    /**
     * Getter of UpdateManager
     *
     * @return WorkerUpdateManager
     */
    public function getUpdateManager()
    {
        if (null === $this->updateManager) {
            $this->updateManager = new WorkerUpdateManager();
        }

        return $this->updateManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('worker-manager:start')
            ->setDescription('Start worker monitoring');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initMonitoring();
        $statusManager = $this->getStatusManager();
        $updateManager = $this->getUpdateManager();
        $logger = $this->getLogger();
        list ($workers, $VMConfigs) = $statusManager->initData();
        $sleepSec = (int) ConfigManager::getConfig()->get('worker_manager.sleep_time');
        if ($sleepSec < 10) {
            $sleepSec = 10;
        }
        $output->writeln('Worker monitoring: <info>running</info>');

        do {
            $statusManager->updateStatus($workers, $VMConfigs);
            $action = $this->getAction();
            switch ($action) {
                case self::ACTION_MONITORING:
                    /** @var \WorkerManager\Model\WorkerConfig $worker */
                    foreach ($workers as $worker) {
                        $updateManager->update($worker);
                        if ($logger) {
                            $logger->logWorker($worker);
                        }
                    }
                    if ($logger) {
                        foreach ($VMConfigs as $VMConfig) {
                            $logger->logVM($VMConfig);
                        }
                    }
                    break;
                case self::ACTION_RESTART:
                    $output->writeln('Restarting workers...');
                    /** @var \WorkerManager\Model\VMConfig $VMConfig */
                    foreach ($VMConfigs as $VMConfig) {
                        $updateManager->restartWorkers(
                            $VMConfig,
                            function ($response) use ($output) {
                                $output->writeln($response);
                            }
                        );
                    }
                    ActionFileManager::updateAction(static::ACTION_MONITORING);
                    break;
            }
        } while ($this->isAlive($sleepSec));
    }

    /**
     * @param int $sleepSec
     *
     * @return bool
     */
    protected function isAlive($sleepSec)
    {
        sleep($sleepSec);

        return true;
    }

    /**
     * init tmp files
     */
    protected function initMonitoring()
    {
        ActionFileManager::updatePid();
        ActionFileManager::updateAction(self::ACTION_MONITORING);
    }
}
