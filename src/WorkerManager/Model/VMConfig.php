<?php

namespace WorkerManager\Model;

/**
 * WorkerManager\Model\VMConfig
 */
class VMConfig
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $port = '8081';

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var int
     */
    protected $maxWorkerCount;

    /**
     * @var WorkerProcess[]
     */
    protected $processes;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Getter of Host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Setter of Host
     *
     * @param string $host
     *
     * @return static
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Getter of Port
     *
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Setter of Port
     *
     * @param string $port
     *
     * @return static
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Getter of Username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Setter of Username
     *
     * @param string $username
     *
     * @return static
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Getter of Password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Setter of Password
     *
     * @param string $password
     *
     * @return static
     */
    public function setPassword($password)
    {
        $this->password = $password;

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
     * Getter of Processes
     *
     * @return WorkerProcess[]
     */
    public function getProcesses()
    {
        return $this->processes;
    }

    /**
     * Setter of Processes
     *
     * @param WorkerProcess[] $processes
     *
     * @return static
     */
    public function setProcesses($processes)
    {
        $this->processes = $processes;

        return $this;
    }

    /**
     * @param WorkerProcess $process
     *
     * @return static
     */
    public function addProcess(WorkerProcess $process)
    {
        $this->processes[] = $process;

        return $this;
    }

    /**
     * @return int
     */
    public function getRunningCount()
    {
        $runningCount = 0;
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $runningCount++;
            }
        }

        return $runningCount;
    }
}
