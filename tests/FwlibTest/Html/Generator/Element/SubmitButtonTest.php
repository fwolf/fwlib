<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\SubmitButton;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SubmitButtonTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|SubmitButton
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(
            SubmitButton::class,
            $methods
        );

        return $mock;
    }


    public function testGetOutputWithJs()
    {
        $element = $this->buildMock();

        $element->setId('foo')
            ->setConfig('sleepTime', 3000);
        $this->assertRegExp('/<script /', $element->getOutput());
    }


    public function testGetOutputWithoutJs()
    {
        $element = $this->buildMock();

        $element->setId('');
        $this->assertNotRegExp('/<script /', $element->getOutput());

        $element->setId('foo')
            ->setConfig('sleepTime', 0);
        $this->assertNotRegExp('/<script /', $element->getOutput());
    }
}
