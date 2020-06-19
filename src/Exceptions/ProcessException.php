<?php

namespace TPG\Attache\Exceptions;

use Throwable;

class ProcessException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
