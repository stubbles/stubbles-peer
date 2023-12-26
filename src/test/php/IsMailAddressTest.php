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
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
/**
 * Tests for stubbles\peer\isMailAddress().
 *
 * @since  7.1.0
 */
#[Group('peer')]
#[Group('mail')]
class IsMailAddressTest extends TestCase
{
    #[Test]
    #[TestWith(['example@example.org'])]
    #[TestWith(['example.foo.bar@example.org'])]
    public function validValueEvaluatesToTrue(string $value): void
    {
        assertTrue(isMailAddress($value));
    }

    #[Test]
    #[TestWith(['space in@mailadre.ss'])]
    #[TestWith(['fäö@mailadre.ss'])]
    #[TestWith(['foo@bar@mailadre.ss'])]
    #[TestWith(['foo..bar@mailadre.ss'])]
    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith(['xcdsfad'])]
    #[TestWith(['.foo.bar@example.org'])]
    #[TestWith(['example@example.org\n'])]
    #[TestWith(['example@exa"mple.org'])]
    #[TestWith(['example@example.org\nBcc: example@example.com'])]
    public function invalidValueEvaluatesToFalse(?string $value): void
    {
        assertFalse(isMailAddress($value));
    }

    #[Test]
    #[TestWith(['Example@example.ORG'])]
    #[TestWith(['Example.Foo.Bar@EXAMPLE.org'])]
    public function validatesIndependendOfLowerOrUpperCase(string $mailAddress): void
    {
        assertTrue(isMailAddress($mailAddress));
    }
}
