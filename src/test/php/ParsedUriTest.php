<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\peer\ParsedUri.
 *
 * @since  5.1.1
 */
#[Group('peer')]
class ParsedUriTest extends TestCase
{
    #[Test]
    public function transposeKeepsChangedParameters(): void
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
     * @since  7.0.0
     */
    #[Test]
    public function canBeCastedToString(): void
    {
        $uri = 'http://example.com/?foo=bar&baz=303';
        $parsedUri = new ParsedUri($uri);
        assertThat((string) $parsedUri, equals($uri));
    }
}
