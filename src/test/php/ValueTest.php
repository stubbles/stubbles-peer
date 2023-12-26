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
use function bovigo\assert\assertTrue;
use function stubbles\values\value;
/**
 * Checks integration with stubbles/values.
 */
#[Group('peer')]
class ValueTest extends TestCase
{
    #[Test]
    public function valueCanBeValidatedAsIpAddress(): void
    {
        assertTrue(value('127.0.0.1')->isIpAddress());
    }

    #[Test]
    public function valueCanBeValidatedAsIpV4Address(): void
    {
        assertTrue(value('127.0.0.1')->isIpV4Address());
    }

    #[Test]
    public function valueCanBeValidatedAsIpV6Address(): void
    {
        assertTrue(
            value('febc:a574:382b:23c1:aa49:4592:4efe:9982')->isIpV6Address()
        );
    }

    #[Test]
    public function valueCanBeValidatesAsMailAddress(): void
    {
        assertTrue(value('example@example.org')->isMailAddress());
    }
}
