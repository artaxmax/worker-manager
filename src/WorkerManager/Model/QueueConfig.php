<?php

namespace WorkerManager\Model;

/**
 * WorkerManager\Model\QueueConfig
 */
class QueueConfig
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $vhostName;

    /**
     * @var RabbitMQVHostConfig
     */
    protected $vhost;

    /**
     * @var int
     */
    protected $messages = 0;

    /**
     * @var int
     */
    protected $publishRate = 0;

    /**
     * @var int
     */
    protected $ackRate = 0;

    /**
     * @var int
     */
    protected $messageRate = 0;

    /**
     * @var array
     */
    protected $stats = [];

    /**
     * @param string $name
     * @param string $vhostName
     */
    public function __construct($name, $vhostName)
    {
        $this->name = $name;
        $this->vhostName = $vhostName;
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
     * Getter of Vhost
     *
     * @return RabbitMQVHostConfig
     */
    public function getVhost()
    {
        return $this->vhost;
    }

    /**
     * Setter of Vhost
     *
     * @param RabbitMQVHostConfig $vhost
     *
     * @return static
     */
    public function setVhost($vhost)
    {
        $this->vhost = $vhost;

        return $this;
    }

    /**
     * Getter of Messages
     *
     * @return int
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Setter of Messages
     *
     * @param int $messages
     *
     * @return static
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Getter of PublishRate
     *
     * @return int
     */
    public function getPublishRate()
    {
        return $this->publishRate;
    }

    /**
     * Setter of PublishRate
     *
     * @param int $publishRate
     *
     * @return static
     */
    public function setPublishRate($publishRate)
    {
        $this->publishRate = $publishRate;

        return $this;
    }

    /**
     * Getter of AckRate
     *
     * @return int
     */
    public function getAckRate()
    {
        return $this->ackRate;
    }

    /**
     * Setter of AckRate
     *
     * @param int $ackRate
     *
     * @return static
     */
    public function setAckRate($ackRate)
    {
        $this->ackRate = $ackRate;

        return $this;
    }

    /**
     * Getter of VhostName
     *
     * @return string
     */
    public function getVhostName()
    {
        return $this->vhostName;
    }

    /**
     * Setter of VhostName
     *
     * @param string $vhostName
     *
     * @return static
     */
    public function setVhostName($vhostName)
    {
        $this->vhostName = $vhostName;

        return $this;
    }

    /**
     * Getter of MessageRate
     *
     * @return int
     */
    public function getMessageRate()
    {
        return $this->messageRate;
    }

    /**
     * Setter of MessageRate
     *
     * @param int $messageRate
     *
     * @return static
     */
    public function setMessageRate($messageRate)
    {
        $this->messageRate = $messageRate;

        return $this;
    }

    /**
     * Getter of Stats
     *
     * @return array
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * Setter of Stats
     *
     * @param array $stats
     *
     * @return static
     */
    public function setStats($stats)
    {
        $this->stats = $stats;

        return $this;
    }
}
