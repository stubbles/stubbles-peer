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
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
/**
 * Tests for stubbles\peer\isMailAddress().
 *
 * @group  peer
 * @group  mail
 * @since  7.1.0
 */
class IsMailAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return  array
     */
    public function validValues()
    {
        return [['example@example.org'],
                ['example.foo.bar@example.org']
        ];
    }

    /**
     * @param  string  $value
     * @test
     * @dataProvider  validValues
     */
    public function validValueEvaluatesToTrue($value)
    {
        assertTrue(isMailAddress($value));
    }

    /**
     * @return  array
     */
    public function invalidValues()
    {
        return [['space in@mailadre.ss'],
                ['fäö@mailadre.ss'],
                ['foo@bar@mailadre.ss'],
                ['foo&/4@mailadre.ss'],
                ['foo..bar@mailadre.ss'],
                [null],
                [''],
                ['xcdsfad'],
                ['foobar@thishost.willnever.exist'],
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

    /**
     * @return  array
     */
    public function mailAddressesWithDifferentCase()
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
    public function validatesIndependendOfLowerOrUpperCase($mailAddress)
    {
        assertTrue(isMailAddress($mailAddress));
    }
}
