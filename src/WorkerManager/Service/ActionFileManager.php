<?php

namespace WorkerManager\Service;

use Symfony\Component\Process\Process;

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
     * @var string
     */
    protected $processName;

    /**
     * @var int
     */
    protected $processPid;

    /**
     * @var string
     */
    protected $actionFilePath;

    /**
     * @param string $processName
     * @param int    $processPid
     * @param string $actionFilePath
     */
    public function __construct(
        $processName,
        $processPid = null,
        $actionFilePath = null
    ) {
        $this->processName = (string) $processName;
        $this->actionFilePath = $actionFilePath ?: dirname(dirname(dirname(__DIR__)));
        $this->processPid = $processPid ?: getmypid();
    }

    /**
     * @return bool
     */
    public function flushPid()
    {
        file_put_contents($this->buildPidPath(), $this->processPid);

        return true;
    }

    /**
     * @param string $action
     *
     * @return bool
     */
    public function flushAction($action)
    {
        file_put_contents($this->buildActionPath(), (string) $action);

        return true;
    }

    /**
     * @return string
     */
    public function loadAction()
    {
        return file_get_contents($this->buildActionPath());
    }

    /**
     * @param array $node
     *
     * @return bool
     */
    public function pingNode(array $node)
    {
        $process = new Process(
            sprintf(
                'ssh %s "cat %s"',
                $node['ssh'],
                $this->buildPidPath($node['action_file_path'], $node['name'])
            )
        );
        $nodePid = $error = null;
        $process->run(
            function ($out, $line) use (&$nodePid, &$error) {
                if ($out === 'out') {
                    $nodePid = $line;
                } else {
                    $error = $line;
                }
            }
        );
        if ($nodePid) {
            $process = new Process(
                sprintf(
                    'ssh %s ps aux | grep "%s" | grep -v grep',
                    $node['ssh'],
                    $nodePid
                )
            );
            $response = $error = null;
            $process->run(
                function ($out, $line) use (&$response, &$error) {
                    if ($out === 'out') {
                        $response = $line;
                    } else {
                        $error = $line;
                    }
                }
            );

            return strpos($response, $nodePid) !== false;
        }

        return false;
    }

    /**
     * @param string $actionFilePath
     * @param string $processName
     *
     * @return string
     */
    protected function buildPidPath($actionFilePath = null, $processName = null)
    {
        $actionFilePath = $actionFilePath ?: $this->actionFilePath;
        $processName = $processName ?: $this->processName;

        return sprintf('%s/worker-monitoring-%s.pid', $actionFilePath, $processName);
    }

    /**
     * @return string
     */
    protected function buildActionPath()
    {
        return sprintf('%s/worker-monitoring-%s.action', $this->actionFilePath, $this->processName);
    }


    /**
     * init tmp files
     *
     * @deprecated
     */
    static public function init()
    {
        static::$pidFile = static::$pidFile ?: dirname(dirname(dirname(__DIR__))).'/worker-monitoring.pid';
        static::$actionFile = static::$actionFile ?: dirname(dirname(dirname(__DIR__))).'/worker-monitoring.action';
    }

    /**
     * @param int $pid
     *
     * @deprecated
     */
    static public function updatePid($pid = null)
    {
        $pid = $pid ?: getmypid();
        file_put_contents(static::$pidFile, $pid);
    }

    /**
     * @param string $action
     *
     * @deprecated
     */
    static public function updateAction($action)
    {
        file_put_contents(static::$actionFile, $action);
    }

    /**
     * @return string
     *
     * @deprecated
     */
    static public function getAction()
    {
        return file_get_contents(static::$actionFile);
    }
}
