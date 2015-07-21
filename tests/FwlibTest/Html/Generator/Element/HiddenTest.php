<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\Hidden;
use Fwlib\Html\Generator\ElementMode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HiddenTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|Hidden
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(
            Hidden::class,
            $methods
        );

        return $mock;
    }


    public function testGetOutput()
    {
        $element = $this->buildMock();

        $element->setClass('foo')
            ->setId('bar')
            ->setName('dummy')
            ->setValue('This is text');

        $output = "<input type='hidden' class='foo' id='bar'
  name='dummy' value='This is text' />";

        $this->assertEquals($output, $element->getOutput(ElementMode::SHOW));
        $this->assertEquals($output, $element->getOutput(ElementMode::EDIT));
    }
}
