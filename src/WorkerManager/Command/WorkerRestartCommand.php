<?php

namespace WorkerManager\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WorkerManager\Service\ActionFileManager;

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

        $action = self::ACTION_RESTART;
        ActionFileManager::updateAction($action);
        if ($input->getOption('wait-response')) {
            while ($action === self::ACTION_RESTART) {
                sleep(5);
                $action = ActionFileManager::getAction();
            }
            $output->writeln('restarted');
        } else {
            $output->writeln('signal sent');
        }
    }
}
