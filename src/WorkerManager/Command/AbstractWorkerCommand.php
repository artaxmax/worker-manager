<?php

namespace WorkerManager\Command;

use Symfony\Component\Console\Command\Command;
use WorkerManager\Service\ConfigManager;

/**
 * WorkerManager\Command\AbstractWorkerCommand
 */
abstract class AbstractWorkerCommand extends Command
{
    const ACTION_MONITORING = 'monitoring';
    const ACTION_RESTART = 'restart-workers';

    /**
     * construct
     */
    public function __construct()
    {
        ConfigManager::register(dirname(__DIR__).'/Resources/config');

        parent::__construct();
    }
}
