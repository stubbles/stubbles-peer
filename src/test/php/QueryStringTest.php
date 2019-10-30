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
use function bovigo\assert\assertEmptyString;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\peer\QueryString.
 *
 * @group  peer
 */
class QueryStringTest extends TestCase
{
    /**
     * empty instance to test
     *
     * @type  QueryString
     */
    protected $emptyQueryString;
    /**
     * prefilled instance to test
     *
     * @type  QueryString
     */
    protected $prefilledQueryString;

    protected function setUp(): void
    {
        $this->emptyQueryString     = new QueryString();
        $this->prefilledQueryString = new QueryString(
                'foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=&set'
        );
    }

    /**
     * @test
     */
    public function constructorThrowsIllegalArgumentExceptionIfQueryStringContainsErrors()
    {
        expect(function() {
                new QueryString('foo.hm=bar&baz[dummy]=blubb&baz[=more&empty=&set');
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function emptyHasNoParametersByDefault()
    {
        assertFalse($this->emptyQueryString->hasParams());
    }

    /**
     * @test
     */
    public function prefilledHasParametersFromInitialQueryString()
    {
        assertTrue($this->prefilledQueryString->hasParams());
    }

    public function parsedParameters(): array
    {
        return [
                ['foo.hm', 'bar'],
                ['baz', ['dummy' => 'blubb', 'more']],
                ['empty', ''],
                ['set', null]
        ];
    }

    /**
     * @test
     * @dataProvider  parsedParameters
     */
    public function parsedParametersAreCorrect(string $paramName, $expectedValue)
    {
        assertThat(
                $this->prefilledQueryString->param($paramName),
                equals($expectedValue)
        );
    }

    /**
     * @test
     */
    public function buildEmptQueryStringReturnsEmptyString()
    {
        assertEmptyString($this->emptyQueryString->build());
    }

    /**
     * @test
     */
    public function buildNonEmptQueryStringReturnsString()
    {
        assertThat(
                $this->prefilledQueryString->build(),
                equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=&set')
        );
    }

    /**
     * @test
     */
    public function checkForNonExistingParamReturnsFalse()
    {
        assertFalse($this->emptyQueryString->containsParam('doesNotExist'));
    }

    /**
     * @test
     */
    public function checkForExistingParamReturnsTrue()
    {
        assertTrue($this->prefilledQueryString->containsParam('foo.hm'));
    }

    /**
     * @test
     */
    public function checkForExistingEmptyParamReturnsTrue()
    {
        assertTrue($this->prefilledQueryString->containsParam('empty'));
    }

    /**
     * @test
     */
    public function checkForExistingNullValueParamReturnsTrue()
    {
        assertTrue($this->prefilledQueryString->containsParam('set'));
    }

    /**
     * @test
     */
    public function getNonExistingParamReturnsNullByDefault()
    {
        assertNull($this->emptyQueryString->param('doesNotExist'));
    }

    /**
     * @test
     */
    public function getNonExistingParamReturnsDefaultValue()
    {
        assertThat(
                $this->emptyQueryString->param('doesNotExist', 'example'),
                equals('example')
        );
    }

    /**
     * @test
     */
    public function getExistingParamReturnsValue()
    {
        assertThat($this->prefilledQueryString->param('foo.hm'), equals('bar'));
    }

    /**
     * @test
     */
    public function removeNonExistingParamDoesNothing()
    {
        assertThat(
                $this->prefilledQueryString->removeParam('doesNotExist')->build(),
                equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=&set')
        );
    }

    /**
     * @test
     */
    public function removeExistingEmptyParam()
    {
        assertThat(
                $this->prefilledQueryString->removeParam('empty')->build(),
                equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&set')
        );
    }

    /**
     * @test
     */
    public function removeExistingNullValueParam()
    {
        assertThat(
                $this->prefilledQueryString->removeParam('set')->build(),
                equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=')
        );
    }

    /**
     * @test
     */
    public function removeExistingArrayParam()
    {
        assertThat(
                $this->prefilledQueryString->removeParam('baz')->build(),
                equals('foo.hm=bar&empty=&set')
        );
    }

    /**
     * @test
     */
    public function addIllegalParamThrowsIllegalArgumentException()
    {
        expect(function() {
                $this->emptyQueryString->addParam('some', new \stdClass());
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     * @since  5.3.1
     */
    public function allowsToAddObjectWithToStringMethodAsParam()
    {
        assertThat(
                $this->emptyQueryString->addParam(
                        'some',
                        new IpAddress('127.0.0.1')
                )->build(),
                equals('some=127.0.0.1')
        );
    }

    /**
     * @test
     */
    public function addNullValueAddsParamNameOnly()
    {
        assertThat(
                $this->emptyQueryString->addParam('some', null)->build(),
                equals('some')
        );
    }

    /**
     * @test
     */
    public function addEmptyValueAddsParamNameAndEqualsign()
    {
        assertThat(
                $this->emptyQueryString->addParam('some', '')->build(),
                equals('some=')
        );
    }

    /**
     * @test
     */
    public function addValueAddsParamNameWithValue()
    {
        assertThat(
                $this->emptyQueryString->addParam('some', 'bar')->build(),
                equals('some=bar')
        );
    }

    /**
     * @test
     */
    public function addArrayAddsParam()
    {
        assertThat(
                $this->emptyQueryString->addParam(
                        'some', ['foo' => 'bar', 'baz']
                )->build(),
                equals('some[foo]=bar&some[]=baz')
        );
    }

    /**
     * @test
     */
    public function addFalseValueTranslatesFalseTo0()
    {
        assertThat(
                $this->emptyQueryString->addParam('some', false)->build(),
                equals('some=0')
        );
    }

    /**
     * @test
     */
    public function addTrueValueTranslatesFalseTo1()
    {
        assertThat(
                $this->emptyQueryString->addParam('some', true)->build(),
                equals('some=1')
        );
    }

    /**
     * @test
     * @since  7.0.0
     */
    public function canBeCastedToString()
    {
        assertThat(
                (string) $this->prefilledQueryString,
                equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=&set')
        );
    }
}
