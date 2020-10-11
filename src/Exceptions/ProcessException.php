<?php

declare(strict_types=1);

namespace TPG\Attache\Exceptions;

use Throwable;

class ProcessException extends \Exception
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct('Failed executing process. '.$message, $code, $previous);
    }
}
