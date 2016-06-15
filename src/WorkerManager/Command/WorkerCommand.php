<?php

namespace WorkerManager\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WorkerManager\Interfaces\LoggerInterface;
use WorkerManager\Service\WorkerStatusManager;
use WorkerManager\Service\WorkerUpdateManager;

/**
 * WorkerManager\Command\WorkerCommand
 */
class WorkerCommand extends Command
{
    const ACTION_MONITORING = 'monitoring';

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
     * @var string
     */
    protected $pidFile;

    /**
     * @var string
     */
    protected $actionFile;

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
    public function setLogger($logger)
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
     * @param string $pidFile
     * @param string $actionFile
     */
    public function __construct($pidFile = null, $actionFile = null)
    {
        $this->pidFile = $pidFile;
        $this->actionFile = $actionFile;
        parent::__construct();
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

        do {
            $statusManager->updateStatus($workers, $VMConfigs);
            $action = $this->getAction();
            switch ($action) {
                case self::ACTION_MONITORING:
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
            }
        } while ($this->isAlive());
    }

    /**
     * @return bool
     */
    protected function isAlive()
    {
        sleep(5);
        return true;
    }

    /**
     * init tmp files
     */
    protected function initMonitoring()
    {
        $this->pidFile = $this->pidFile ?: dirname(dirname(dirname(__DIR__))).'/worker-monitoring.pid';
        $this->actionFile = $this->actionFile ?: dirname(dirname(dirname(__DIR__))).'/worker-monitoring.action';
        file_put_contents($this->pidFile, getmypid());
        file_put_contents($this->actionFile, self::ACTION_MONITORING);
    }

    /**
     * @return string
     */
    protected function getAction()
    {
        return file_get_contents($this->actionFile);
    }
}
