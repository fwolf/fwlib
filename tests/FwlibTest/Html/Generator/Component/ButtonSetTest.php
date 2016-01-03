<?php
namespace FwlibTest\Html\Generator\Component;

use Fwlib\Html\Generator\Component\ButtonSet;
use Fwlib\Html\Generator\Element\Button;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ButtonSetTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|Button
     */
    protected function buildButtonMock(array $methods = null)
    {
        $mock = $this->getMock(
            Button::class,
            $methods
        );

        return $mock;
    }


    /**
     * @param   string[] $methods
     * @return  MockObject|ButtonSet
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(
            ButtonSet::class,
            $methods
        );

        return $mock;
    }


    public function testAddAndPrependAndRemove()
    {
        $buttonSet = $this->buildMock();


        $button = $this->buildButtonMock()
            ->setName('foo1');
        $buttonSet->add($button);
        $this->assertEquals(1, $buttonSet->count());


        $button = $this->buildButtonMock()
            ->setName('foo2');
        $buttonSet->add($button);
        $this->assertEquals(2, $buttonSet->count());


        $button = $this->buildButtonMock()
            ->setName('foo3');
        $buttonSet->prepend($button);
        $buttons = $buttonSet->getButtons();
        $this->assertEquals(3, $buttonSet->count());

        $this->assertEqualArray(
            ['foo3', 'foo1', 'foo2'],
            array_keys($buttons)
        );

        $buttonSet->remove('foo2')
            ->remove('foo3');
        $buttons = $buttonSet->getButtons();
        $this->assertEqualArray(['foo1'], array_keys($buttons));
    }


    public function testGetOutput()
    {
        $button = $this->buildButtonMock(['getOutput']);
        $button->expects($this->once())
            ->method('getOutput');

        $buttonSet = $this->buildMock();
        $buttonSet->add($button);

        $buttonSet->setContainerClass('foo')
            ->setContainerId('bar')
            ->setContainerTag('tag')
            ->setIndent(3);

        $output = $buttonSet->getOutput();

        $this->assertRegExp("/'foo'/", $output);
        $this->assertRegExp("/'bar'/", $output);
        $this->assertRegExp("/   <tag /", $output);
        $this->assertRegExp("/   <\\/tag>/", $output);
    }


    public function testGetOutputWithJs()
    {
        $buttonSet = $this->buildMock();

        $buttonSet->setContainerClass('foo')
            ->setSleepTime(3000);
        $this->assertRegExp('/<script /', $buttonSet->getOutput());
    }


    public function testGetOutputWithoutJs()
    {
        $buttonSet = $this->buildMock();

        $buttonSet->setContainerClass('')
            ->setSleepTime(3000);
        $this->assertNotRegExp('/<script /', $buttonSet->getOutput());

        $buttonSet->setContainerClass('foo')
            ->setSleepTime(0);
        $this->assertNotRegExp('/<script /', $buttonSet->getOutput());
    }
}
