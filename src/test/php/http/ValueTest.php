<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer\http;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertTrue;
use function stubbles\values\value;
/**
 * Checks integration with stubbles/values.
 *
 * @group  peer
 * @group  peer_http
 */
class ValueTest extends TestCase
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
