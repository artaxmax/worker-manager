<?php

namespace WorkerManager\Helper;

use WorkerManager\Interfaces\RoutingHelperInterface;
use WorkerManager\Service\WorkerStatusManager;

/**
 * WorkerManager\Helper\MonitoringHelper
 */
class MonitoringHelper implements RoutingHelperInterface
{
    /**
     * @var WorkerStatusManager
     */
    protected $statusManager;

    /**
     * @param WorkerStatusManager $statusManager
     */
    public function __construct(WorkerStatusManager $statusManager)
    {
        $this->statusManager = $statusManager;
    }

    /**
     * @return array
     */
    public function getData()
    {
        list ($workers, $VMConfigs) = $this->statusManager->initData();
        $this->statusManager->updateStatus($workers, $VMConfigs);

        return [
            'workers' => $workers,
        ];
    }
}
