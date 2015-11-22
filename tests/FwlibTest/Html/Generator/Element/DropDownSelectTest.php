<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\DropDownSelect;
use Fwlib\Html\Generator\ElementMode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class DropDownSelectTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|DropDownSelect
     */
    protected function buildMock(array $methods = null)
    {
        /** @var MockObject|DropDownSelect $mock */
        $mock = $this->getMockBuilder(DropDownSelect::class)
            ->setMethods($methods)
            ->setConstructorArgs(['exampleSelect'])
            ->getMock();

        $mock->setClass('foo')
            ->setId('bar')
            ->setConfig(DropDownSelect::KEY_ITEMS, [
                1 => 'A',
                2 => 'B',
            ]);

        return $mock;
    }


    public function testGetOutputForEditMode()
    {
        $element = $this->buildMock();

        $element->setConfig(DropDownSelect::KEY_PROMPT, 'Select One')
            ->setValue(2);

        $expected = <<<TAG
<select class='foo' id='bar'
  name='exampleSelect'>
  <option value='' class='foo__title'>Select One</option>
  <option value='1' class='foo__title' id='bar__title--1'>A</option>
  <option value='2' selected='selected' class='foo__title' id='bar__title--2'>B</option>
</select>
TAG;

        $this->assertEquals($expected, $element->getOutput(ElementMode::EDIT));
    }


    public function testGetOutputForShowMode()
    {
        $element = $this->buildMock();

        $element->setConfig(DropDownSelect::KEY_TAG, 'p');

        $expected = <<<TAG
<input type='hidden' id='bar' name='exampleSelect' value='' />
<p class='foo__title' id='bar__title'>Invalid Item</p>
TAG;
        $this->assertEquals($expected, $element->getOutput(ElementMode::SHOW));


        $element->setValue(1);
        $expected = <<<TAG
<input type='hidden' id='bar' name='exampleSelect' value='1' />
<p class='foo__title' id='bar__title'>A</p>
TAG;
        $this->assertEquals($expected, $element->getOutput(ElementMode::SHOW));


        $element->setConfig(DropDownSelect::KEY_TAG, '');
        $expected = <<<TAG
A
TAG;
        $this->assertEquals($expected, $element->getOutput(ElementMode::SHOW));
    }
}
