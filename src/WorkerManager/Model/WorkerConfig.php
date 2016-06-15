<?php

namespace WorkerManager\Model;

/**
 * WorkerManager\Model\WorkerConfig
 */
class WorkerConfig
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var WorkerProcess[]
     */
    protected $process;

    /**
     * @var int
     */
    protected $minWorkerCount = 1;

    /**
     * @var int
     */
    protected $maxWorkerCount = 1;

    /**
     * @var int
     */
    protected $runningCount = 0;

    /**
     * @var QueueConfig
     */
    protected $queue;

    /**
     * @var int
     *
     * @ODM\Int
     */
    protected $priority = 0;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Getter of Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter of Name
     *
     * @param string $name
     *
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Getter of Process
     *
     * @return WorkerProcess[]
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Setter of Process
     *
     * @param WorkerProcess[] $process
     *
     * @return static
     */
    public function setProcess($process)
    {
        $this->process = $process;

        return $this;
    }

    /**
     * @param WorkerProcess $process
     *
     * @return static
     */
    public function addProcess(WorkerProcess $process)
    {
        $this->process[] = $process;
        if ($process->isRunning()) {
            $this->runningCount++;
        }

        return $this;
    }

    /**
     * Getter of MinWorkerCount
     *
     * @return int
     */
    public function getMinWorkerCount()
    {
        return $this->minWorkerCount;
    }

    /**
     * Setter of MinWorkerCount
     *
     * @param int $minWorkerCount
     *
     * @return static
     */
    public function setMinWorkerCount($minWorkerCount)
    {
        $this->minWorkerCount = $minWorkerCount;

        return $this;
    }

    /**
     * Getter of MaxWorkerCount
     *
     * @return int
     */
    public function getMaxWorkerCount()
    {
        return $this->maxWorkerCount;
    }

    /**
     * Setter of MaxWorkerCount
     *
     * @param int $maxWorkerCount
     *
     * @return static
     */
    public function setMaxWorkerCount($maxWorkerCount)
    {
        $this->maxWorkerCount = $maxWorkerCount;

        return $this;
    }

    /**
     * Getter of RunningCount
     *
     * @return int
     */
    public function getRunningCount()
    {
        return $this->runningCount;
    }

    /**
     * Getter of Queue
     *
     * @return QueueConfig
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Setter of Queue
     *
     * @param QueueConfig $queue
     *
     * @return static
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * @return static
     */
    public function incRunningCount()
    {
        $this->runningCount++;

        return $this;
    }
    /**
     * @return static
     */
    public function decRunningCount()
    {
        $this->runningCount--;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMaxWorkerCount()
    {
        return $this->runningCount === $this->maxWorkerCount;
    }

    /**
     * @return bool
     */
    public function isMinWorkerCount()
    {
        return $this->runningCount === $this->minWorkerCount;
    }

    /**
     * Getter of Priority
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Setter of Priority
     *
     * @param int $priority
     *
     * @return static
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Setter of RunningCount
     *
     * @param int $runningCount
     *
     * @return static
     */
    public function setRunningCount($runningCount)
    {
        $this->runningCount = $runningCount;

        return $this;
    }
}
