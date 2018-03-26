<?php

namespace UniversalTechnology\Bowler\Contracts;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @author Kinane Domloje <kinane@vinelab.com>
 */
interface BowlerExceptionHandler
{
    public function reportQueue(Exception $e, AMQPMessage $message);
}
