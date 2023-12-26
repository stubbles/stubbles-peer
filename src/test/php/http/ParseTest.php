<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer\http;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stubbles\values\Parse;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;


#[Group('peer')]
#[Group('peer_http')]
class ParseTest extends TestCase
{
    #[Test]
    public function parseRecognizesHttpUris(): void
    {
        assertThat(
            Parse::toType('http://example.net/'),
            equals(HttpUri::fromString('http://example.net/'))
        );
    }

    #[Test]
    public function parseRecognizesHttpsUris(): void
    {
        assertThat(
            Parse::toType('https://example.net/'),
            equals(HttpUri::fromString('https://example.net/'))
        );
    }
}
