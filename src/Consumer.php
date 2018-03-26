<?php

namespace UniversalTechnology\Bowler;

use UniversalTechnology\Bowler\Traits\AdminTrait;
use UniversalTechnology\Bowler\Traits\DeadLetteringTrait;
use UniversalTechnology\Bowler\Traits\CompileParametersTrait;
use UniversalTechnology\Bowler\Exceptions\Handler as BowlerExceptionHandler;

define('RABBITMQ_HOST', '127.0.0.1');
define('RABBITMQ_PORT', '5672');
define('RABBITMQ_USERNAME', 'guest');
define('RABBITMQ_PASSWORD', 'guest');
define('EXCHANGE_NAME', 'logs');

/**
 * Bowler Consumer.
 *
 * @author Ali Issa <ali@vinelab.com>
 * @author Kinane Domloje <kinane@vinelab.com>
 */
class Consumer
{
    use AdminTrait;
    use DeadLetteringTrait;
    use CompileParametersTrait;

    /**
     * The main class of the package where we define the channel and the connection.
     *
     * @var UniversalTechnology\Bowler\Connection
     */
    private $connection;

    /**
     * The name of the queue bound to the exchange where the producer sends its messages.
     *
     * @var string
     */
    private $queueName;

    /**
     * The name of the exchange where the producer sends its messages to.
     *
     * @var string
     */
    private $exchangeName;

    /**
     * The binding keys used by the exchange to route messages to bounded queues.
     *
     * @var string
     */
    private $bindingKeys;

    /**
     * type of exchange:
     * fanout: routes messages to all of the queues that are bound to it and the routing key is ignored.
     *
     * direct: delivers messages to queues based on the message routing key. A direct exchange is ideal for the unicast routing of messages (although they can be used for multicast routing as well)
     *
     * default: a direct exchange with no name (empty string) pre-declared by the broker. It has one special property that makes it very useful for simple applications: every queue that is created is automatically bound to it with a routing key which is the same as the queue name
     *
     * topic: route messages to one or many queues based on matching between a message routing key and the pattern that was used to bind a queue to an exchange. The topic exchange type is often used to implement various publish/subscribe pattern variations. Topic exchanges are commonly used for the multicast routing of messages
     *
     * @var string
     */
    private $exchangeType;

    /**
     * If set, the server will reply with Declare-Ok if the exchange already exists with the same name, and raise an error if not. The client can use this to check whether an exchange exists without modifying the server state.
     *
     * @var bool
     */
    private $passive;

    /**
     * If set when creating a new exchange, the exchange will be marked as durable. Durable exchanges remain active when a server restarts. Non-durable exchanges (transient exchanges) are purged if/when a server restarts.
     *
     * @var bool
     */
    private $durable;

    /**
     * If set, the exchange is deleted when all queues have finished using it.
     *
     * @var bool
     */
    private $autoDelete;

    /**
     * The arguments that should be added to the `queue_declare` statement for dead lettering.
     *
     * @var array
     */
    private $arguments = [];

    /**
     * @param UniversalTechnology\Bowler\Connection $connection
     * @param string                    $queueName
     * @param string                    $exchangeName
     * @param string                    $exchangeType
     * @param array                     $bindingKeys
     * @param bool                      $passive
     * @param bool                      $durable
     * @param bool                      $autoDelete
     */
    public function __construct(Connection $connection, $queueName, $exchangeName, $exchangeType = 'fanout', $bindingKeys = [], $passive = false, $durable = true, $autoDelete = false)
    {
        $this->connection = $connection;
        $this->queueName = $queueName;
        $this->exchangeName = $exchangeName;
        $this->exchangeType = $exchangeType;
        $this->bindingKeys = $bindingKeys;
        $this->passive = $passive;
        $this->durable = $durable;
        $this->autoDelete = $autoDelete;
    }

    /**
     * consume a message from a specified exchange.
     *
     * @param string                            $handlerClass
     * @param UniversalTechnology\Bowler\Exceptions\Handler $exceptionHandler
     */
    public function listenToQueue($handlerClass, BowlerExceptionHandler $exceptionHandler)
    {
        $channel = $this->connection->getChannel();

        $channel->exchange_declare(
            EXCHANGE_NAME,
            'fanout', # type
            false, # passive
            false, # durable
            false# auto_delete
        );

        list($queue_name) = $channel->queue_declare(
            '', # queue
            false, # passive
            false, # durable
            true, # exclusive
            false# auto delete
        );

        $channel->queue_bind($queue_name, EXCHANGE_NAME);
        print 'Waiting for wiki. To exit press CTRL+C' . PHP_EOL;

        // Instantiate Handler
        $queueHandler = app($handlerClass);

        $callback = function ($msg) use ($queueHandler) {
            // print_r(json_decode($msg->body));
            $queueHandler->handle($msg);
        };

        $channel->basic_consume(
            $queue_name,
            '',
            false,
            true,
            false,
            false,
            $callback
        );

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
    }
}
