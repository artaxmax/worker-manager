<?php

namespace WorkerManager\Interfaces;

use WorkerManager\Application;

/**
 * WorkerManager\Interfaces\ComponentInterface
 */
interface ComponentInterface
{
    /**
     * @param Application $application
     */
    public function registry(Application $application);
}
