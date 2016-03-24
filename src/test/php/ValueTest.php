<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\peer
 */
namespace stubbles\peer;
use function bovigo\assert\assertTrue;
use function stubbles\values\value;
/**
 * Checks integration with stubbles/values.
 *
 * @group  peer
 */
class ValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function valueCanBeValidatedAsIpAddress()
    {
        assertTrue(value('127.0.0.1')->isIpAddress());
    }

    /**
     * @test
     */
    public function valueCanBeValidatedAsIpV4Address()
    {
        assertTrue(value('127.0.0.1')->isIpV4Address());
    }

    /**
     * @test
     */
    public function valueCanBeValidatedAsIpV6Address()
    {
        assertTrue(
                value('febc:a574:382b:23c1:aa49:4592:4efe:9982')->isIpV6Address()
        );
    }

    /**
     * @test
     */
    public function valueCanBeValidatesAsMailAddress()
    {
        assertTrue(value('example@example.org')->isMailAddress());
    }
}
