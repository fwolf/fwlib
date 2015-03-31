<?php
namespace FwlibTest\Html\ListView\Helper;

use Fwlib\Config\Config;
use Fwlib\Html\ListView\Helper\ClassAndIdConfigTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ClassAndIdConfigTraitTest extends PHPUnitTestCase
{
    /**
     * @var Config
     */
    protected $config;


    /**
     * @return MockObject | ClassAndIdConfigTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(ClassAndIdConfigTrait::class)
            ->setMethods(['getConfig', 'setConfig'])
            ->getMockForTrait();

        $this->config = new Config;

        $mock->expects($this->any())
            ->method('getConfig')
            ->willReturnCallback(function($key) {
                return $this->config->get($key);
            });

        $mock->expects($this->any())
            ->method('setConfig')
            ->willReturnCallback(function($key, $value) {
                $this->config->set($key, $value);

                return $this->returnSelf();
            });

        return $mock;
    }


    public function testSetterAndGetter()
    {
        $listView = $this->buildMock();

        $listView->setClass('fooList');
        $this->assertEquals(
            'fooList',
            $this->reflectionCall($listView, 'getClass')
        );
        $this->assertEquals(
            'fooList__pager',
            $this->reflectionCall($listView, 'getClass', ['pager'])
        );

        $listView->setId(42);
        $this->assertEquals(
            'fooList-42',
            $this->reflectionCall($listView, 'getId')
        );
        $this->assertEquals(
            'fooList-42__pager',
            $this->reflectionCall($listView, 'getId', ['pager'])
        );
    }
}
