<?php

namespace UniversalTechnology\Bowler\Exceptions;

use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Exception\AMQPProtocolConnectionException;
use UniversalTechnology\Bowler\Contracts\BowlerExceptionHandler as ExceptionHandler;

/**
 * @author Kinane Domloje <kinane@vinelab.com>
 */
class Handler
{
    /**
     * The BowlerExceptionHandler contract bound app's exception handler.
     */
    private $exceptionHandler;

    public function __construct(ExceptionHandler $handler)
    {
        $this->exceptionHandler = $handler;
    }

    /**
     * Map php-mqplib exceptions to Bowler's.
     *
     * @param \Exception $e
     * @param array      $parameters
     * @param array      $arguments
     *
     * @return mix
     */
    public function handleServerException(\Exception $e, $parameters = [], $arguments = [])
    {
        if ($e instanceof AMQPProtocolChannelException) {
            $e = new DeclarationMismatchException($e, $parameters, $arguments);
        } elseif ($e instanceof AMQPProtocolConnectionException) {
            $e = new InvalidSetupException($e, $parameters, $arguments);
        } else {
            $e = new BowlerGeneralException($e, $parameters, $arguments);
        }

        throw $e;
    }

    /**
     * Report error to the app's exceptions Handler.
     *
     * @param \Exception                         $e
     * @param mix PhpAmqpLib\Message\AMQPMessage $message
     */
    public function reportError($e, $message)
    {
        if (method_exists($this->exceptionHandler, 'reportQueue')) {
            $this->exceptionHandler->reportQueue($e, $message);
        }
    }
}
