<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\Radio;
use Fwlib\Html\Generator\ElementMode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RadioTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|Radio
     */
    protected function buildMock(array $methods = null)
    {
        /** @var MockObject|Radio $mock */
        $mock = $this->getMock(
            Radio::class,
            $methods
        );

        $mock->setClass('foo')
            ->setId('bar')
            ->setName('dummy')
            ->setConfig('default', 0)
            ->setConfig('values', ['Value 0', 'Value 1']);

        return $mock;
    }


    public function testGetOutputForEditMode()
    {
        $element = $this->buildMock();

        $expectedOutput = <<<TAG
<fieldset class='foo__container' id='bar__container'>
  <input type='radio' class='foo' id='bar--0'
    name='dummy' value='0' checked='checked' />
  <label class='foo__title' id='bar__title--0'
    for='bar--0'>Value 0</label>
  <input type='radio' class='foo' id='bar--1'
    name='dummy' value='1' />
  <label class='foo__title' id='bar__title--1'
    for='bar--1'>Value 1</label>
</fieldset>
TAG;
        $this->assertEquals(
            $expectedOutput,
            $element->getOutput(ElementMode::EDIT)
        );
    }


    public function testGetOutputForShowMode()
    {
        $element = $this->buildMock();

        $element->setValue(1);

        $expectedOutput = <<<TAG
<input type='hidden' id='bar' name='dummy' value='1' />
<span class='foo__title' id='bar__title--1'>Value 1</span>
TAG;
        $this->assertEquals(
            $expectedOutput,
            $element->getOutput(ElementMode::SHOW)
        );
    }
}
