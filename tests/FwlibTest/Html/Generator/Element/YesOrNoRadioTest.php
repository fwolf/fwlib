<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\YesOrNoRadio;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class YesOrNoRadioTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|YesOrNoRadio
     */
    protected function buildMock(array $methods = null)
    {
        /** @var MockObject|YesOrNoRadio $mock */
        $mock = $this->getMock(
            YesOrNoRadio::class,
            $methods
        );

        $mock->setClass('foo')
            ->setId('bar')
            ->setName('dummy')
            ->setConfig('default', YesOrNoRadio::NO);

        return $mock;
    }


    public function testGetDefaultConfigs()
    {
        $element = $this->buildMock();

        $items = $this->reflectionCall($element, 'getItems');
        $this->assertEquals(2, count($items));
    }


    public function testGetTitleClass()
    {
        $element = $this->buildMock();

        // Use default value
        $titleClass = $this->reflectionCall($element, 'getTitleClass');
        $expected = 'foo__title ' . YesOrNoRadio::HTML_CLASS_NO;
        $this->assertEquals($expected, $titleClass);

        $element->setValue(YesOrNoRadio::YES);
        $titleClass = $this->reflectionCall($element, 'getTitleClass');
        $expected = 'foo__title ' . YesOrNoRadio::HTML_CLASS_YES;
        $this->assertEquals($expected, $titleClass);

        // Invalid value, eg -1 for non selected
        $element->setValue(-1);
        $titleClass = $this->reflectionCall($element, 'getTitleClass');
        $expected = 'foo__title';
        $this->assertEquals($expected, $titleClass);
    }
}
