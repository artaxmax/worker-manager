<?php

namespace WorkerManager\Model;

/**
 * WorkerManager\Model\WorkerProcess
 */
class WorkerProcess
{
    const STATUS_RUNNING = 'RUNNING';
    const STATUS_STOPPED = 'STOPPED';
    const STATUS_STARTING = 'STARTING';

    /**
     * @var VMConfig
     */
    protected $VMConfig;

    /**
     * @var string
     */
    protected $processName;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $PID;

    /**
     * @var string
     */
    protected $upTime;

    /**
     * @param string $processName
     * @param string $status
     * @param string $PID
     * @param string $upTime
     */
    public function __construct(
        $processName,
        $status,
        $PID,
        $upTime
    ) {
        $this->processName = $processName;
        $this->status = $status;
        $this->PID = $PID;
        $this->upTime = $upTime;
    }

    /**
     * Getter of VMConfig
     *
     * @return VMConfig
     */
    public function getVMConfig()
    {
        return $this->VMConfig;
    }

    /**
     * Setter of VMConfig
     *
     * @param VMConfig $VMConfig
     *
     * @return static
     */
    public function setVMConfig(VMConfig $VMConfig)
    {
        $this->VMConfig = $VMConfig;
        $VMConfig->addProcess($this);

        return $this;
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return $this->status !== self::STATUS_STOPPED;
    }

    /**
     * Getter of ProcessName
     *
     * @return string
     */
    public function getProcessName()
    {
        return $this->processName;
    }

    /**
     * Setter of ProcessName
     *
     * @param string $processName
     *
     * @return static
     */
    public function setProcessName($processName)
    {
        $this->processName = $processName;

        return $this;
    }

    /**
     * Getter of Status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Setter of Status
     *
     * @param string $status
     *
     * @return static
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Getter of PID
     *
     * @return string
     */
    public function getPID()
    {
        return $this->PID;
    }

    /**
     * Setter of PID
     *
     * @param string $PID
     *
     * @return static
     */
    public function setPID($PID)
    {
        $this->PID = $PID;

        return $this;
    }

    /**
     * Getter of UpTime
     *
     * @return string
     */
    public function getUpTime()
    {
        return $this->upTime;
    }

    /**
     * Setter of UpTime
     *
     * @param string $upTime
     *
     * @return static
     */
    public function setUpTime($upTime)
    {
        $this->upTime = $upTime;

        return $this;
    }
}
