<?php

declare(strict_types=1);

namespace PhpArchiveStream\Exceptions;

use Exception;

/**
 * The base exception for all php-archive-stream errors.
 *
 * Catching this exception catches every error thrown by the library.
 *
 * @api
 */
class ArchiveStreamException extends Exception {}
