<?php

namespace WorkerManager\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WorkerManager\Interfaces\LoggerInterface;
use WorkerManager\Service\WorkerMonitoring;
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
        $monitoring = WorkerMonitoring::init();

        $statusManager = $this->getStatusManager();
        $updateManager = $this->getUpdateManager();
        $logger = $this->getLogger();
        list ($workers, $VMConfigs) = $statusManager->initData();

        $output->writeln('Worker monitoring: <info>running</info>');
        $output->write('Balancing: ');
        if ($monitoring->isBalancing()) {
            $output->writeln('<info>yes</info>');
        } else {
            $output->writeln('<comment>no</comment>');
        }
        $output->writeln('Node name: <info>'.$monitoring->getName().'</info>');
        $output->writeln('Node PID: <info>'.$monitoring->getPID().'</info>');
        $output->write('Status: ');

        do {
            if ($monitoring->isMaster()) {
                $output->write('<info>master</info> ');
                $statusManager->updateStatus($workers, $VMConfigs);
                switch ($monitoring->getAction()) {
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
                            $output->writeln('Restart ' . $VMConfig->getName());
                            $updateManager->restartWorkers(
                                $VMConfig,
                                function ($response) use ($output) {
                                    $output->writeln($response);
                                }
                            );
                        }
                        $monitoring->updateAction(static::ACTION_MONITORING);
                        break;
                }
            } else {
                $output->write('<info>slave</info> ');
            }
            $monitoring->wait();
        } while ($this->isAlive());
    }

    /**
     * @return bool
     */
    protected function isAlive()
    {
        return true;
    }
}
