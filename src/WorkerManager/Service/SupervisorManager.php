<?php

namespace WorkerManager\Service;

use Symfony\Component\Process\Process;
use WorkerManager\Model\VMConfig;

/**
 * WorkerManager\Service\SupervisorManager
 */
class SupervisorManager
{
    /**
     * @var bool
     */
    protected $useSudo = false;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param VMConfig $VMConfig
     *
     * @return static
     */
    static public function init(
        VMConfig $VMConfig
    ) {
        $server = $VMConfig->getServer();

        $manager = new static(
            $server,
            $VMConfig->getUsername(),
            $VMConfig->getPassword(),
            $VMConfig->getConfig()
        );
        if ($VMConfig->hasUseSudo()) {
            $manager->setUseSudo(true);
        }

        return $manager;
    }

    /**
     * @param string $server
     * @param string $username
     * @param string $password
     * @param string $config
     */
    public function __construct($server, $username, $password, $config)
    {
        $this->options = array_filter(
            [
                's' => $server,
                'u' => $username,
                'p' => $password,
                'c' => $config,
            ]
        );
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

    /**
     * @return array|null
     */
    public function getHostStatus()
    {
        $process = $this->createProcess('');
        $response = '';
        $process->run(
            function ($type, $line) use (&$response) {
                if ($type === 'out') {
                    $response .= $line;
                }
            }
        );
        $status = $this->parseStatus($response);
        if (count($status) > 0) {
            return $status;
        }

        return null;
    }

    /**
     * @param string $command
     *
     * @return array|null
     */
    public function runCommand($command)
    {
        $process = $this->createProcess($command);
        $response = '';
        $process->run(
            function ($type, $line) use (&$response) {
                if ($type === 'out') {
                    $response .= $line;
                }
            }
        );

        return $response;
    }

    /**
     * @param string $command
     * @param array  $options
     *
     * @return Process
     */
    protected function createProcess($command = '', array $options = [])
    {
        $options = array_merge($this->options, $options);
        $commandVar = $this->useSudo ? ['sudo', 'supervisorctl'] : ['supervisorctl'];
        $commandParams = [];
        foreach ($options as $name => $value) {
            $commandVar[] = '-'.$name.' %s';
            $commandParams[] = $value;
        }
        $commandVar[] = $command;
        $process = new Process(
            call_user_func_array('sprintf', array_merge([implode(' ', $commandVar)], $commandParams))
        );

        return $process;
    }

    /**
     * @param string $result
     *
     * @return array
     */
    protected function parseStatus($result)
    {
        $lines = explode("\n", $result);
        $result = [];
        foreach ($lines as $line) {
            $data = array_values(array_filter(explode(' ', $line)));
            $dataCount = count($data);
            if ($dataCount >= 6) {
                $result[] = [
                    'name'   => $data[0],
                    'status' => $data[1],
                    'pid'    => preg_replace('~[^0-9]~', '', $data[3]),
                    'uptime' => implode(' ', array_splice($data, 5)),
                ];
            } elseif ($dataCount === 2) {
                $result[] = [
                    'name'   => $data[0],
                    'status' => $data[1],
                    'pid'    => null,
                    'uptime' => null,
                ];
            }
        }

        return $result;
    }
}
