<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\peer
 */
namespace stubbles\peer\http;
use function bovigo\assert\assertTrue;
use function stubbles\values\value;
/**
 * Checks integration with stubbles/values.
 *
 * @group  peer
 * @group  peer_http
 */
class ValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function valueCanBeValidatedAsHttpUri()
    {
        assertTrue(value('http://example.net/')->isHttpUri());
    }

    /**
     * @test
     */
    public function valueCanBeValidatedAsExistingHttpUri()
    {
        assertTrue(value('http://localhost/')->isExistingHttpUri());
    }
}
