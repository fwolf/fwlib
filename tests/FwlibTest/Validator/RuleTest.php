<?php
namespace FwlibTest\Validator;

use Fwlib\Validator\Rule;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RuleTest extends PHPUnitTestCase
{
    /**
     * @param   string[]    $methods
     * @return  MockObject|Rule
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(
            Rule::class,
            $methods
        );

        return $mock;
    }


    public function testConstructor()
    {
        /** @var MockObject|Rule $rule */
        $rule = $this->getMock(
            Rule::class,
            null,
            [
                'regex dummy: foo, bar=false',
                Rule::FIELD_SEPARATOR,
                Rule::OPTION_SEPARATOR
            ]
        );

        $this->assertEquals('regex', $rule->getType());
        $this->assertEquals('dummy', $rule->getField());
        $this->assertTrue($rule->getOption('foo'));
        $this->assertFalse($rule->getOption('bar'));
        $this->assertEqualArray(
            ['foo' => true, 'bar' => false],
            $rule->getOptions()
        );
    }


    public function testExport()
    {
        $rule = $this->buildMock();

        $rule->import(
            'regex dummy: foo, bar=false',
            Rule::FIELD_SEPARATOR,
            Rule::OPTION_SEPARATOR
        );
        $this->assertEquals(
            'regex dummy: foo=true,bar=false',
            $rule->export(Rule::FIELD_SEPARATOR, Rule::OPTION_SEPARATOR)
        );

        $rule->import(
            'regex dummy1',
            Rule::FIELD_SEPARATOR,
            Rule::OPTION_SEPARATOR
        );
        $this->assertEquals(
            'regex dummy1',
            $rule->export(Rule::FIELD_SEPARATOR, Rule::OPTION_SEPARATOR)
        );
    }


    public function testImport()
    {
        $rule = $this->buildMock();
        $rule->import(
            'regex dummy: foo, bar=false',
            Rule::FIELD_SEPARATOR,
            Rule::OPTION_SEPARATOR
        );

        $this->assertEquals('regex', $rule->getType());
        $this->assertEquals('dummy', $rule->getField());
        $this->assertTrue($rule->getOption('foo'));
        $this->assertFalse($rule->getOption('bar'));
        $this->assertEqualArray(
            ['foo' => true, 'bar' => false],
            $rule->getOptions()
        );


        // Test clear()
        $rule->import(
            'regex dummy1',
            Rule::FIELD_SEPARATOR,
            Rule::OPTION_SEPARATOR
        );
        $this->assertEquals('dummy1', $rule->getField());
        $this->assertFalse($rule->getOption('foo'));
        $this->assertEqualArray([], $rule->getOptions());

        $rule->import(
            'notEmpty',
            Rule::FIELD_SEPARATOR,
            Rule::OPTION_SEPARATOR
        );
        $this->assertEquals('notEmpty', $rule->getType());
        $this->assertEquals('', $rule->getField());
        $this->assertEqualArray([], $rule->getOptions());
    }


    /**
     * @expectedException \Fwlib\Validator\Exception\InvalidRuleStringException
     */
    public function testImportWithInvalidFormat()
    {
        $rule = $this->buildMock();
        $rule->import(
            ': notEmpty',
            Rule::FIELD_SEPARATOR,
            Rule::OPTION_SEPARATOR
        );
    }
}
