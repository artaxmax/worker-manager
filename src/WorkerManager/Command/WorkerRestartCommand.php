<?php

namespace WorkerManager\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WorkerManager\Service\WorkerMonitoring;

/**
 * WorkerManager\Command\WorkerRestartCommand
 */
class WorkerRestartCommand extends AbstractWorkerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('worker-manager:restart')
            ->setDescription('Restart workers')
            ->addOption('wait-response', null, InputOption::VALUE_NONE, 'Wait till workers will be restarted');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Restart workers</info>');

        $monitoring = WorkerMonitoring::init();
        if ($monitoring->isMaster()) {
            $action = self::ACTION_RESTART;
            $monitoring->updateAction($action);
            if ($input->getOption('wait-response')) {
                while ($action === self::ACTION_RESTART) {
                    sleep(5);
                    $action = $monitoring->getAction();
                }
                $output->writeln('restarted');
            } else {
                $output->writeln('signal sent');
            }
        } else {
            $output->writeln('not master node');
        }
    }
}
