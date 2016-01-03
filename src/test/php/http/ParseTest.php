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
use stubbles\values\Parse;

use function bovigo\assert\assert;
use function bovigo\assert\predicate\equals;
/**
 * @group  peer
 * @group  peer_http
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function parseRecognizesHttpUris()
    {
        assert(
                Parse::toType('http://example.net/'),
                equals(HttpUri::fromString('http://example.net/'))
        );
    }

    /**
     * @test
     */
    public function parseRecognizesHttpsUris()
    {
        assert(
                Parse::toType('https://example.net/'),
                equals(HttpUri::fromString('https://example.net/'))
        );
    }
}
