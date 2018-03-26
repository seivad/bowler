<?php

namespace UniversalTechnology\Bowler\Exceptions;

use UniversalTechnology\Bowler\Exception\BowlerGeneralException;

/**
 * @author Kinane Domloje <kinane@vinelab.com>
 */
class InvalidSubscriberBindingException extends BowlerGeneralException
{
    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }
}
