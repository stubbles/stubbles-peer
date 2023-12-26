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

use function bovigo\assert\assertTrue;
use function stubbles\values\value;
/**
 * Checks integration with stubbles/values.
 */
#[Group('peer')]
#[Group('peer_http')]
class ValueTest extends TestCase
{
    #[Test]
    public function valueCanBeValidatedAsHttpUri(): void
    {
        assertTrue(value('http://example.net/')->isHttpUri());
    }

    #[Test]
    public function valueCanBeValidatedAsExistingHttpUri(): void
    {
        assertTrue(value('http://localhost/')->isExistingHttpUri());
    }
}
