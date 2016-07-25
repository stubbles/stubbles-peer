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
 * Exception to be thrown when an error on a network connection occurs.
 */
class ConnectionFailure extends \Exception
{
    /**
     * constructor
     *
     * @param  string      $message
     * @param  \Throwable  $previous
     * @param  int         $code
     */
    public function __construct(string $message, \Throwable $previous = null, int $code = 1)
    {
        parent::__construct($message, $code, $previous);
    }
}
