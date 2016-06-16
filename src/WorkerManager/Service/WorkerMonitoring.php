<?php

namespace WorkerManager\Service;

use Noodlehaus\Config;

/**
 * WorkerManager\Service\WorkerMonitoring
 */
class WorkerMonitoring
{
    /**
     * @var ActionFileManager
     */
    protected $actionFile;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var int
     */
    protected $PID;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $balancing = false;

    /**
     * @var int
     */
    protected $sleepSec;

    /**
     * @param Config $config
     * @param string $name
     */
    public function __construct(Config $config, $name)
    {
        $this->config = $config;
        $this->name = $name;
        $this->PID = getmypid();
        $this->balancing = (bool) $config->get('worker_manager.monitoring.balancing');
        $this->sleepSec = (int) $config->get('worker_manager.sleep_time');
        if ($this->sleepSec < 10) {
            $this->sleepSec = 10;
        }
        $this->actionFile = new ActionFileManager(
            $this->name,
            $this->PID,
            $config->get('worker_manager.monitoring.action_file_path')
        );
        $this->actionFile->flushPid();
    }

    /**
     * @param string $name
     *
     * @return static
     */
    static public function init($name = null)
    {
        $config = ConfigManager::getConfig();
        $name = $name ?: $config->get('worker_manager.monitoring.node');

        return new static($config, $name);
    }

    /**
     * @return bool
     */
    public function isMaster()
    {
        if (false === $this->balancing) {
            return true;
        }
        $nodes = $this->config->get('worker_manager.monitoring.nodes');
        $masterName = null;
        foreach ($nodes as $node) {
            if ($node['name'] === $this->name || $this->actionFile->pingNode($node)) {
                $masterName = $node['name'];
                break;
            }
        }

        return ($masterName === $this->name);
    }

    /**
     * @return bool
     */
    public function wait()
    {
        sleep($this->sleepSec);

        return true;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->actionFile->loadAction();
    }

    /**
     * @param string $action
     *
     * @return string
     */
    public function updateAction($action)
    {
        return $this->actionFile->flushAction($action);
    }

    /**
     * Getter of Balancing
     *
     * @return boolean
     */
    public function isBalancing()
    {
        return $this->balancing;
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
     * Getter of PID
     *
     * @return int
     */
    public function getPID()
    {
        return $this->PID;
    }

    /**
     * Setter of PID
     *
     * @param int $PID
     *
     * @return static
     */
    public function setPID($PID)
    {
        $this->PID = $PID;

        return $this;
    }
}
