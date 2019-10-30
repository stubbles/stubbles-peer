<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\peer\ParsedUri.
 *
 * @group  peer
 * @since  5.1.1
 */
class ParsedUriTest extends TestCase
{
    /**
     * @test
     */
    public function transposeKeepsChangedParameters()
    {
        $parsedUri = new ParsedUri('http://example.com/?foo=bar&baz=303');
        $parsedUri->queryString()->addParam('baz', '313');
        $parsedUri->queryString()->addParam('dummy', 'example');
        assertThat(
                $parsedUri->transpose(['scheme' => 'https'])
                        ->asStringWithoutPort(),
                equals('https://example.com/?foo=bar&baz=313&dummy=example')
        );
    }

    /**
     * @test
     * @since  7.0.0
     */
    public function canBeCastedToString()
    {
        $uri = 'http://example.com/?foo=bar&baz=303';
        $parsedUri = new ParsedUri($uri);
        assertThat((string) $parsedUri, equals($uri));
    }
}
