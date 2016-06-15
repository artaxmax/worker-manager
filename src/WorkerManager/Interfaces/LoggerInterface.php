<?php

namespace WorkerManager\Interfaces;

use WorkerManager\Model\VMConfig;
use WorkerManager\Model\WorkerConfig;

/**
 * WorkerManager\Interfaces\LoggerInterface
 */
interface LoggerInterface
{
    /**
     * @param WorkerConfig $worker
     *
     * @return void
     */
    public function logWorker(WorkerConfig $worker);

    /**
     * @param VMConfig $config
     *
     * @return void
     */
    public function logVM(VMConfig $config);
}
