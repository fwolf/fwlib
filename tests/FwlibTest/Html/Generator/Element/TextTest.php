<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\Text;
use Fwlib\Html\Generator\ElementMode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 */
class TextTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|Text
     */
    protected function buildMock(array $methods = null)
    {
        /** @var MockObject|Text $mock */
        $mock = $this->getMock(
            Text::class,
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
            "<input type='text' class='foo' id='bar'
  name='dummy' value='This is text' />",
            $element->getOutput(ElementMode::EDIT)
        );
    }


    public function testGetOutputForShowMode()
    {
        $element = $this->buildMock();

        $this->assertEquals(
            "This&nbsp;is&nbsp;text",
            $element->getOutput(ElementMode::SHOW)
        );

        $element->setConfig('tag', 'p');
        $this->assertEquals(
            "<p class='foo' id='bar'>This&nbsp;is&nbsp;text</p>",
            $element->getOutput(ElementMode::SHOW)
        );
    }
}
