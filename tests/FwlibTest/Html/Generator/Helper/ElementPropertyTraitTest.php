<?php
namespace FwlibTest\Html\Generator\Helper;

use Fwlib\Html\Generator\Helper\ElementPropertyTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ElementPropertyTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[]    $methods
     * @return  MockObject|ElementPropertyTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(ElementPropertyTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testAccessors()
    {
        $trait = $this->buildMock();

        $trait->setComment('foo');
        $this->assertEquals('foo', $trait->getComment());

        $trait->setName('bar');
        $this->assertEquals('bar', $trait->getName());

        $trait->setTip('foo');
        $this->assertEquals('foo', $trait->getTip());

        $trait->setValidateRules(['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $trait->getValidateRules());
    }


    public function testSetGetValue()
    {
        $trait = $this->buildMock(['getConfig']);
        $trait->expects($this->any())
            ->method('getConfig')
            ->willReturnOnConsecutiveCalls(null, null, 'foo');

        // Both value and default value are null
        $trait->setValue(null);
        $this->assertNull($trait->getValue());

        // Only default is null
        $trait->setValue('bar');
        $this->assertEquals('bar', $trait->getValue());

        // Only value is null, should use default value
        $trait->setValue(null);
        $this->assertEquals('foo', $trait->getValue());
    }
}
