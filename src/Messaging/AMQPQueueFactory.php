<?php

namespace Simgroep\EventSourcing\Messaging;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPConnection;
use Spray\Serializer\SerializerInterface;

class AMQPQueueFactory
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var AbstractConnection
     */
    private $connection;

    /**
     * @var array<AMQPChannel>
     */
    private $channels = array();

    /**
     * @param SerializerInterface $serializer
     * @param AbstractConnection  $connection
     */
    public function __construct(
        SerializerInterface $serializer,
        AbstractConnection $connection)
    {
        $this->serializer = $serializer;
        $this->connection = $connection;
    }

    /**
     * @param string $exchange
     * @param string $routingKey
     * @param string $queue
     *
     * @return AMQPQueue
     */
    public function build($exchange, $routingKey, $queue)
    {
        return new AMQPQueue(
            $this->serializer,
            $this->buildChannel($queue),
            $exchange,
            $routingKey,
            $queue
        );
    }

    /**
     * @param string $queue
     *
     * @return AMQPChannel
     */
    public function buildChannel($queue)
    {
        $this->channels[] = $channel = $this->connection->channel();
        $channel->queue_declare($queue, false, false, false, false);
        return $channel;
    }

    /**
     * Close all connections.
     *
     * @return void
     */
    public function __destruct()
    {
        foreach ($this->channels as $channel) {
            $channel->close();
        }
        $this->connection->close();
    }
}