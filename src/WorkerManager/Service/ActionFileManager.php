<?php

namespace WorkerManager\Service;

/**
 * WorkerManager\Service\ActionFileManager
 */
class ActionFileManager
{
    const TYPE_PID = 'pid';
    const TYPE_ACTION = 'action';

    /**
     * @var string
     */
    static public $pidFile;

    /**
     * @var string
     */
    static public $actionFile;

    /**
     * init tmp files
     */
    static public function init()
    {
        static::$pidFile = static::$pidFile ?: dirname(dirname(dirname(__DIR__))).'/worker-monitoring.pid';
        static::$actionFile = static::$actionFile ?: dirname(dirname(dirname(__DIR__))).'/worker-monitoring.action';
    }

    /**
     * @param int $pid
     */
    static public function updatePid($pid = null)
    {
        $pid = $pid ?: getmypid();
        file_put_contents(static::$pidFile, $pid);
    }

    /**
     * @param string $action
     */
    static public function updateAction($action)
    {
        file_put_contents(static::$actionFile, $action);
    }

    /**
     * @return string
     */
    static public function getAction()
    {
        return file_get_contents(static::$actionFile);
    }
}
