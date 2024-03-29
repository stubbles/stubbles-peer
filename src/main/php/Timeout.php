<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;

use Throwable;

/**
 * Exception to be thrown when a timeout on a network connection occurs.
 *
 * @since  6.0.0
 */
class Timeout extends ConnectionFailure
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous, 2);
    }
}
