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
    protected $server;

    /**
     * @var string
     */
    protected $config;

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
     * @var bool
     */
    protected $useSudo = false;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
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

    /**
     * Getter of Config
     *
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Setter of Config
     *
     * @param string $config
     *
     * @return static
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Getter of Server
     *
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Setter of Server
     *
     * @param string $server
     *
     * @return static
     */
    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Getter of UseSudo
     *
     * @return boolean
     */
    public function hasUseSudo()
    {
        return $this->useSudo;
    }

    /**
     * Setter of UseSudo
     *
     * @param boolean $useSudo
     *
     * @return static
     */
    public function setUseSudo($useSudo)
    {
        $this->useSudo = $useSudo;

        return $this;
    }
}
