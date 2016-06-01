<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\ReadonlyText;
use Fwlib\Html\Generator\ElementMode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ReadonlyTextTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|ReadonlyText
     */
    protected function buildMock(array $methods = null)
    {
        /** @var MockObject|ReadonlyText $mock */
        $mock = $this->getMock(
            ReadonlyText::class,
            $methods
        );

        $mock->setClass('foo')
            ->setId('bar')
            ->setName('dummy')
            ->setValue('This is text');

        return $mock;
    }


    public function testGetOutputForEditMode()
    {
        $element = $this->buildMock();

        $this->assertEquals(
            "This&nbsp;is&nbsp;text",
            $element->getOutput(ElementMode::EDIT)
        );

        $element->setConfig('tag', 'p');
        $output = <<<TAG
<input type='hidden'
  name='dummy' value='This is text' />
<p class='foo' id='bar'>This&nbsp;is&nbsp;text</p>
TAG;
        $this->assertEquals(
            $output,
            $element->getOutput(ElementMode::EDIT)
        );
    }
}
