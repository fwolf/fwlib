<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\Button;
use Fwlib\Html\Generator\ElementMode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ButtonTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|Button
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(
            Button::class,
            $methods
        );

        return $mock;
    }


    public function testGetOutputForShowMode()
    {
        $element = $this->buildMock();

        $element->setClass('foo')
            ->setId('bar')
            ->setName('dummy')
            ->setValue('This is a button');

        $this->assertEquals(
            "<button type='button' class='foo' id='bar'
  name='dummy'>
  This&nbsp;is&nbsp;a&nbsp;button</button>",
            $element->getOutput(ElementMode::SHOW)
        );
    }
}
