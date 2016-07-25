<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\peer
 */
namespace stubbles\peer;
/**
 * Exception to be thrown when the protocol of a connection is violated.
 *
 * @since  8.0.0
 */
class ProtocolViolation extends ConnectionFailure
{
    /**
     * constructor
     *
     * @param  string      $message
     * @param  \Throwable  $previous
     */
    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct($message, $previous, 3);
    }
}