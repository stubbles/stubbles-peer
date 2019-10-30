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
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
/**
 * Tests for stubbles\peer\isMailAddress().
 *
 * @group  peer
 * @group  mail
 * @since  7.1.0
 */
class IsMailAddressTest extends TestCase
{
    public function validValues(): array
    {
        return [['example@example.org'],
                ['example.foo.bar@example.org']
        ];
    }

    /**
     * @test
     * @dataProvider  validValues
     */
    public function validValueEvaluatesToTrue(string $value)
    {
        assertTrue(isMailAddress($value));
    }

    public function invalidValues(): array
    {
        return [['space in@mailadre.ss'],
                ['fäö@mailadre.ss'],
                ['foo@bar@mailadre.ss'],
                ['foo..bar@mailadre.ss'],
                [null],
                [''],
                ['xcdsfad'],
                ['.foo.bar@example.org'],
                ['example@example.org\n'],
                ['example@exa"mple.org'],
                ['example@example.org\nBcc: example@example.com']
        ];
    }

    /**
     * @param  string  $value
     * @test
     * @dataProvider  invalidValues
     */
    public function invalidValueEvaluatesToFalse($value)
    {
        assertFalse(isMailAddress($value));
    }

    public function mailAddressesWithDifferentCase(): array
    {
        return [
            ['Example@example.ORG'],
            ['Example.Foo.Bar@EXAMPLE.org']
        ];
    }

    /**
     * @test
     * @dataProvider  mailAddressesWithDifferentCase
     */
    public function validatesIndependendOfLowerOrUpperCase(string $mailAddress)
    {
        assertTrue(isMailAddress($mailAddress));
    }
}
