<?php
namespace FwlibTest\Validator\Helper;

use Fwlib\Config\StringOptions;
use Fwlib\Validator\Helper\FieldAndOptionsPropertyTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class FieldAndOptionsPropertyTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[]    $methods
     * @return  MockObject|\Fwlib\Validator\Helper\FieldAndOptionsPropertyTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(
            FieldAndOptionsPropertyTrait::class
        )
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testAccessors()
    {
        $trait = $this->buildMock();

        $trait->setField('dummy field');
        $this->assertEquals('dummy field', $trait->getField());

        // When options instance is null
        $this->assertFalse($trait->getOption('notExist'));
        $this->assertEquals(42, $trait->getOption('notExist', 42));

        $optionString = 'foo = 42, bar';
        $options = new StringOptions($optionString);
        $trait->setOptionsInstance($options);
        $this->assertEquals(42, $trait->getOption('foo'));
        $this->assertTrue($trait->getOption('bar'));
        $this->assertTrue(is_array($trait->getOptions()));
        $this->assertInstanceOf(
            StringOptions::class,
            $trait->getOptionsInstance()
        );

        // When key is not exist in options instance
        $this->assertFalse($trait->getOption('notExist'));
        $this->assertEquals(42, $trait->getOption('notExist', 42));
    }
}
