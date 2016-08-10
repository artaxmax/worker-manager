<?php

namespace WorkerManager\Service;

use WorkerManager\Model\VMConfig;
use WorkerManager\Model\WorkerConfig;
use WorkerManager\Model\WorkerProcess;

/**
 * WorkerManager\Service\WorkerUpdateManager
 */
class WorkerUpdateManager
{
    /**
     * @param WorkerConfig $worker
     *
     * @return bool
     * @throws \Exception
     */
    public function update(WorkerConfig $worker)
    {
        if ($worker->getRunningCount() < $worker->getMinWorkerCount()) {
            $this->updateWorkerCount($worker, $worker->getMinWorkerCount());

            return true;
        }
        $queue = $worker->getQueue();
        $stats = $queue->getStats();
        if (empty($stats)) {
            $statsSum = $minStats = $maxStats = 0;
        } else {
            $statsSum = array_sum($stats);
            $minStats = min($stats);
            $maxStats = max($stats);
        }
        if ($queue->getMessages() > 0) {
            if ($worker->getRunningCount() === 0) {
                $this->updateWorkerCount($worker, $worker->getRunningCount() + 1);
            } else {
                // message count grow up
                if ($this->isGrowUp($worker)) {
                    $this->updateWorkerCount($worker, $worker->getMaxWorkerCount());
                // message count lower then worker count
                } elseif ($maxStats < $worker->getRunningCount()) {
                    $this->updateWorkerCount($worker, $worker->getRunningCount() - 1);
                // min count (for configured interval) more then 0
                } elseif ($minStats > 0 && !$worker->isMaxWorkerCount()) {
                    $this->updateWorkerCount($worker, $worker->getRunningCount() + 1);
                }
            }
        } elseif ($statsSum == 0 && !$worker->isMinWorkerCount()) {
            $this->updateWorkerCount($worker, $worker->getRunningCount() - 1);
        }

        return true;
    }

    /**
     * @param VMConfig $VMConfig
     * @param callable $callback
     */
    public function restartWorkers(VMConfig $VMConfig, $callback = null)
    {
        $workersProcesses = [];
        foreach ($VMConfig->getProcesses() as $process) {
            if ($process->isRunning()) {
                $workersProcesses[] = $process->getProcessName();
            }
        }
        $response = SupervisorManager::init($VMConfig)->runCommand(
            sprintf('restart %s', implode(' ', $workersProcesses))
        );
        if (is_callable($callback)) {
            $callback($response);
        }
    }

    /**
     * @param WorkerConfig $worker
     * @param int          $count
     *
     * @throws \Exception
     */
    protected function updateWorkerCount(WorkerConfig $worker, $count)
    {
        if ($worker->getMaxWorkerCount() < $count || $worker->getMinWorkerCount() > $count) {
            return;
        }
        if ($worker->getRunningCount() > $count) {
            $processes = $this->getProcesses($worker, $worker->getRunningCount() - $count);
            foreach ($processes as $process) {
                $this->stopProcess($process);
                $worker->decRunningCount();
            }
        } elseif ($worker->getRunningCount() < $count) {
            $processes = $this->getProcesses($worker, $count - $worker->getRunningCount(), false);
            foreach ($processes as $process) {
                $this->startProcess($process);
                $worker->incRunningCount();
            }
        }
    }

    /**
     * @param WorkerConfig $worker
     * @param int          $count
     * @param bool         $running
     *
     * @return WorkerProcess[]
     */
    protected function getProcesses(WorkerConfig $worker, $count, $running = true)
    {
        $processByPriority = [];
        $sort = [];
        foreach ($worker->getProcess() as $process) {
            if ($process->isRunning() === $running) {
                $VMConfig = $process->getVMConfig();
                $runningCount = $VMConfig->getRunningCount();
                if ($running || $VMConfig->getMaxWorkerCount() > $runningCount) {
                    $processByPriority[] = $process;
                    $sort[] = $runningCount;
                }
            }
        }
        if ($running) {
            array_multisort($sort, SORT_DESC, $processByPriority);
        } else {
            array_multisort($sort, SORT_ASC, $processByPriority);
        }
        $result = array_slice($processByPriority, 0, $count);

        return $result;
    }

    /**
     * @param WorkerProcess $process
     */
    protected function stopProcess(WorkerProcess $process)
    {
        $VMConfig = $process->getVMConfig();
        SupervisorManager::init($VMConfig)->runCommand(
            sprintf('stop %s', $process->getProcessName())
        );
        $process->setStatus(WorkerProcess::STATUS_STOPPED);
    }

    /**
     * @param WorkerProcess $process
     */
    protected function startProcess(WorkerProcess $process)
    {
        $VMConfig = $process->getVMConfig();
        SupervisorManager::init($VMConfig)->runCommand(
            sprintf('start %s', $process->getProcessName())
        );
        $process->setStatus(WorkerProcess::STATUS_RUNNING);
    }

    /**
     * @param WorkerConfig $worker
     *
     * @return bool
     */
    protected function isGrowUp(WorkerConfig $worker)
    {
        $stats = $worker->getQueue()->getStats();
        if (reset($stats) >= end($stats)) {
            return false;
        }
        $count = reset($stats);
        do {
            $next = next($stats);
            if (false === $next) {
                break;
            }
            if ($next < $count) {
                return false;
            }
            $count = $next;
        } while (false !== $next);

        return true;
    }
    /**
     * @param WorkerConfig $worker
     *
     * @return bool
     */
    protected function isSlowDown(WorkerConfig $worker)
    {
        $stats = $worker->getQueue()->getStats();
        if (reset($stats) <= end($stats)) {
            return false;
        }
        $count = reset($stats);
        do {
            $next = next($stats);
            if (false === $next) {
                break;
            }
            if ($next > $count) {
                return false;
            }
            $count = $next;
        } while (false !== $next);

        return true;
    }
}
