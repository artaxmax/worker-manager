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
            $statsSum = $minStats = $maxStats = $first = $last = 0;
        } else {
            $statsSum = array_sum($stats);
            $minStats = min($stats);
            $maxStats = max($stats);
            $first = reset($stats);
            $last = end($stats);
        }
        if ($queue->getMessages() > 0) {
            if ($worker->getRunningCount() === 0) {
                $this->updateWorkerCount($worker, $worker->getRunningCount() + 1);
            } else {
                $grow = $this->isGrowUp($worker) || ($minStats > 0 && $first > $last);
                if ($grow && !$worker->isMaxWorkerCount()) {
                    $this->updateWorkerCount($worker, $worker->getRunningCount() + 1);

                    return true;
                }
                if ($maxStats < $worker->getRunningCount()) {
                    $this->updateWorkerCount($worker, $worker->getRunningCount() - 1);
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
        $response = SupervisorManager::init(
            $VMConfig->getHost(),
            $VMConfig->getPort(),
            $VMConfig->getUsername(),
            $VMConfig->getPassword()
        )->runCommand(
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
        if ($worker->getMaxWorkerCount() < $count) {
            throw new \Exception(
                sprintf(
                    'Unable to update %s worker count to %s. Max count: %s',
                    $worker->getName(),
                    $count,
                    $worker->getMaxWorkerCount()
                )
            );
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
        SupervisorManager::init(
            $VMConfig->getHost(),
            $VMConfig->getPort(),
            $VMConfig->getUsername(),
            $VMConfig->getPassword()
        )->runCommand(
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
        SupervisorManager::init(
            $VMConfig->getHost(),
            $VMConfig->getPort(),
            $VMConfig->getUsername(),
            $VMConfig->getPassword()
        )->runCommand(
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
