<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;
/**
 * Class for URIs and methods on URIs.
 *
 * Use Uri::fromString() to create an instance.
 *
 * @internal
 */
class ConstructedUri extends Uri
{
    /**
     * constructor
     *
     * @param  \stubbles\peer\ParsedUri  $uri
     */
    protected function __construct(ParsedUri $uri)
    {
        $this->parsedUri = $uri;
    }
}
