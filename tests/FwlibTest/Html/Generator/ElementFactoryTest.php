<?php
namespace FwlibTest\Html\Generator;

use Fwlib\Html\Generator\Element\Text;
use Fwlib\Html\Generator\ElementFactory;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ElementFactoryTest extends PHPunitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|ElementFactory
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(
            ElementFactory::class,
            $methods
        );

        return $mock;
    }


    public function testCreate()
    {
        $factory = $this->buildMock();

        $element = $factory->create('text');
        $this->assertInstanceOf(Text::class, $element);

        $element = $factory->create('\\stdClass');
        $this->assertInstanceOf(\stdClass::class, $element);
    }


    /**
     * @expectedException \Fwlib\Html\Generator\Exception\ElementNotFoundException
     */
    public function testCreateNotImplementedElement()
    {
        $factory = $this->buildMock();

        $factory->create('notExistElement');
    }
}
