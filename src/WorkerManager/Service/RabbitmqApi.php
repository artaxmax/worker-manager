<?php

namespace WorkerManager\Service;

/**
 * WorkerManager\Service\RabbitmqApi
 */
class RabbitmqApi
{
    const METHOD_EXCHANGES = 'exchanges';
    const METHOD_QUEUES = 'queues';

    /**
     * @var array
     */
    protected $connections = [];

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $hostTemplate;

    /**
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     * @param string $hostTemplate
     */
    public function __construct($host, $port, $username, $password, $hostTemplate)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->hostTemplate = $hostTemplate;
    }

    /**
     * @param string $vhost
     * @param string $port
     * @param string $host
     * @param string $hostTemplate
     * @param string $username
     * @param string $password
     *
     * @throws \Exception
     */
    public function addVhost(
        $vhost,
        $port = null,
        $host = null,
        $hostTemplate = null,
        $username = null,
        $password = null
    ) {
        if (isset($this->connections[$vhost])) {
            throw new \Exception(sprintf('Vhost %s already added', $vhost));
        }
        $this->connections[$vhost] = [
            'host' => $host ? $host : $this->host,
            'port' => $port ? $port : $this->port,
            'username' => $username ? $username : $this->username,
            'password' => $password ? $password : $this->password,
            'hostTemplate' => $hostTemplate ? $hostTemplate : $this->hostTemplate,
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getExchangesConfig()
    {
        $options = array();
        $vhosts = array_keys($this->connections);
        foreach ($vhosts as $vhost) {
            $options[$vhost] = array();
            $exchanges = $this->loadExchange($vhost);
            if ($exchanges === false) {
                throw new \Exception(sprintf('Unable to load "%s" vhost', $vhost));
            }
            $list = json_decode($exchanges, true);
            if ($list) {
                foreach ($list as $option) {
                    $options[$vhost][$option['name']] = $option;
                }
            }
        }

        return $options;
    }

    /**
     * @param string|array $queueName
     * @param string       $vhost
     *
     * @return int
     */
    public function getQueueMessagesCount($queueName, $vhost)
    {
        if (is_array($queueName)) {
            return $this->getVHostQueues(
                $vhost,
                function (array $queue) use ($queueName) {
                    return in_array($queue['name'], $queueName);
                },
                function (array $queue) {
                    return $queue['messages'];
                }
            );
        }

        return $this->getQueueInfo(
            $queueName,
            $vhost,
            function (array $queue) {
                if (isset($queue['messages']) === false) {
                    $queue['messages'] = 0;
                }

                return $queue['messages'];
            }
        );
    }

    /**
     * @param string   $vhost
     * @param callable $queryFilter
     * @param callable $dataFilter
     * @param array    $query
     *
     * @return array
     */
    public function getVHostQueues($vhost, $queryFilter = null, $dataFilter = null, $query = [])
    {
        $queryFilter = $queryFilter ?: function () {
            return true;
        };
        $dataFilter = $dataFilter ?: function (array $queue) {
            return $queue;
        };
        $queueInfo = $this->callApi(
            $this->buildUrl(self::METHOD_QUEUES, $vhost, array()),
            true,
            $query
        );
        $result = [];
        if (is_array($queueInfo)) {
            foreach ($queueInfo as $queue) {
                if ($queryFilter($queue)) {
                    $result[$queue['name']] = $dataFilter($queue);
                }
            }
        }

        return $result;
    }

    /**
     * @param string   $queueName
     * @param string   $vhost
     * @param callable $dataFilter
     * @param array    $query
     *
     * @return array
     */
    public function getQueueInfo($queueName, $vhost, $dataFilter = null, $query = [])
    {
        $dataFilter = $dataFilter ?: function (array $queue) {
            return $queue;
        };
        $queueInfo = $this->callApi(
            $this->buildUrl(self::METHOD_QUEUES, $vhost, array($queueName)),
            true,
            $query
        );
        if (is_array($queueInfo)) {
            return $dataFilter($queueInfo);
        }

        return $queueInfo;
    }

    /**
     * @param string      $vhost
     * @param string|null $exchange
     *
     * @return bool|string
     */
    protected function loadExchange($vhost, $exchange = null)
    {
        $options = array();
        if ($exchange) {
            $options[] = $exchange;
        }
        try {
            return $this->callApi($this->buildUrl(self::METHOD_EXCHANGES, $vhost, $options));
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $url
     * @param bool   $json
     * @param array  $query
     *
     * @return bool|mixed|string
     */
    private function callApi($url, $json = false, $query = [])
    {
        $curl = curl_init(sprintf('%s?%s', $url, http_build_query($query)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERPWD, sprintf('%s:%s', $this->username, $this->password));
        $response = curl_exec($curl);

        if ($json) {
            $response = json_decode($response, true);
        }

        return $response;
    }

    /**
     * @param string $method
     * @param string $vhost
     * @param array  $options
     *
     * @return string
     */
    private function buildUrl($method, $vhost, $options = array())
    {
        $connection = $this->getConnection($vhost);
        $url = strtr($connection['hostTemplate'], ['host' => $connection['host'], 'port' => $connection['port']]);
        $url = $url.'/api/'.$method.'/'.$vhost;
        if ($options) {
            $url = $url.'/'.implode('/', $options);
        }

        if (strpos($url, '://') === false) {
            $url = 'https://'.$url;
        }

        return $url;
    }

    /**
     * @param string $vhost
     *
     * @return array
     * @throws \Exception
     */
    private function getConnection($vhost)
    {
        if (false === isset($this->connections[$vhost])) {
            throw new \Exception(sprintf('Unexpected vhost: %s', $vhost));
        }

        return $this->connections[$vhost];
    }
}
