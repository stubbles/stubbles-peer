<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;

use Exception;
use Throwable;

/**
 * Exception to be thrown when an error on a network connection occurs.
 */
class ConnectionFailure extends Exception
{
    public function __construct(
        string $message,
        ?Throwable $previous = null,
        int $code = 1
    ) {
        parent::__construct($message, $code, $previous);
    }
}
