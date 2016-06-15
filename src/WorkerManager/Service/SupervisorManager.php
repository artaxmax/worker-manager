<?php

namespace WorkerManager\Service;

use Symfony\Component\Process\Process;

/**
 * WorkerManager\Service\SupervisorManager
 */
class SupervisorManager
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $port;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     *
     * @return static
     */
    static public function init($host, $port, $username, $password)
    {
        return new static($host, $port, $username, $password);
    }

    /**
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     */
    public function __construct($host, $port, $username, $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return array
     */
    public function getVMStatus()
    {
        $process = new Process(sprintf('ping -c 1 -W 5 %s', escapeshellarg($this->host)));

        return ($process->run() === 0);
    }

    /**
     * @return array|null
     */
    public function getHostStatus()
    {
        $process = $this->createProcess($this->host, '');
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
        $process = $this->createProcess($this->host, $command);
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
     * @param string $host
     * @param string $command
     * @param string $port
     * @param string $username
     * @param string $password
     *
     * @return Process
     */
    protected function createProcess($host, $command, $port = '8081', $username = null, $password = null)
    {
        $process = new Process(
            sprintf(
                'supervisorctl -s http://%s:%s -u %s -p %s %s',
                $host,
                $port,
                $username ?: $this->username,
                $password ?: $this->password,
                $command
            )
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
