<?php

namespace UniversalTechnology\Bowler\Exceptions;

use UniversalTechnology\Bowler\Exceptions\BowlerGeneralException;

/**
 * @author Kinane Domloje <kinane@vinelab.com>
 */
class UnregisteredQueueException extends BowlerGeneralException
{
    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }
}
