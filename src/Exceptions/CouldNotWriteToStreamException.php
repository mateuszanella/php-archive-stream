<?php

namespace PhpArchiveStream\Exceptions;

use Exception;

class CouldNotWriteToStreamException extends Exception
{
    public function __construct()
    {
        parent::__construct('Could not write to stream');
    }
}
