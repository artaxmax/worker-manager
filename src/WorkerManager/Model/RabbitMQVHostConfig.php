<?php

namespace WorkerManager\Model;

/**
 * WorkerManager\Model\RabbitMQVHostConfig
 */
class RabbitMQVHostConfig
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
     * @var string
     */
    protected $hostTemplate = 'http://[host]:[port]';

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
     * Getter of HostTemplate
     *
     * @return string
     */
    public function getHostTemplate()
    {
        return $this->hostTemplate;
    }

    /**
     * Setter of HostTemplate
     *
     * @param string $hostTemplate
     *
     * @return static
     */
    public function setHostTemplate($hostTemplate)
    {
        $this->hostTemplate = $hostTemplate;

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
}
