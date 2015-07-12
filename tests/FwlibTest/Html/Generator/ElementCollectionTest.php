<?php
namespace FwlibTest\Html\Generator;

use Fwlib\Html\Generator\AbstractElement;
use Fwlib\Html\Generator\ElementCollection;
use Fwlib\Html\Generator\ElementInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ElementCollectionTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @param   string   $name
     * @return  MockObject|ElementInterface
     */
    protected function buildElementMock(array $methods = null, $name = '')
    {
        if (is_null($methods)) {
            $methods = [];
        }
        $methods[] = 'getOutputForShowMode';

        $mock = $this->getMockBuilder(AbstractElement::class)
            ->setMethods($methods)
            ->setConstructorArgs([$name])
            ->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getOutputForShowMode')
            ->willReturn("<div>$name</div>");

        return $mock;
    }


    /**
     * @param   string[] $methods
     * @return  MockObject|ElementCollection
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(
            ElementCollection::class,
            $methods
        );

        return $mock;
    }


    public function testArrayAccess()
    {
        $elements = $this->buildMock();

        $this->assertFalse(isset($elements['name']));
        $this->assertTrue(empty($elements['name']));

        $element = $this->buildElementMock(null, 'name');
        $elements['name'] = $element;

        $this->assertTrue(isset($elements['name']));
        $this->assertFalse(empty($elements['name']));

        unset($elements['name']);
        $this->assertFalse(isset($elements['name']));
    }


    public function testGetOutput()
    {
        $outStr = '<div>foo</div>';

        $element = $this->buildElementMock(['getOutput'], 'name');
        $element->expects($this->exactly(2))
            ->method('getOutput')
            ->willReturn($outStr);

        $element1 = clone $element;
        $element1->setName('name1');

        $elements = $this->buildMock();
        $elements['name'] = $element;
        $elements['name1'] = $element1;
        $elements->setIndent(2);

        $output = $elements->getOutput();

        $this->assertEquals(
            '  ' . $outStr . PHP_EOL . '  ' . $outStr,
            $output
        );
    }


    public function testPrepare()
    {
        $element = $this->buildElementMock(null, 'name');

        $elements = $this->buildMock();
        $elements->setClass('foo')
            ->setId('bar')
            ->setMode('edit');

        $this->reflectionCall($elements, 'prepare', [$element]);
        $this->assertEquals('foo__edit', $element->getClass());
        $this->assertEquals('bar-name', $element->getId());


        $element->setClass('class')
            ->setId('id');

        $this->reflectionCall($elements, 'prepare', [$element]);
        $this->assertEquals('class', $element->getClass());
        $this->assertEquals('id', $element->getId());
    }


    public function testRootPathAccessors()
    {
        $elements = $this->buildMock();

        $elements->setRootPath('foo/bar/');
        $this->assertEquals('foo/bar/', $elements->getRootPath());
    }
}
