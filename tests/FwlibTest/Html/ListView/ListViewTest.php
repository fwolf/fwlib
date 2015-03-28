<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\ListView;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ListViewTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ListView
     */
    protected function buildMock()
    {
        $mock = $this->getMock(ListView::class, null);

        return $mock;
    }


    public function testAccessors()
    {
        $listView = $this->buildMock();

        $this->assertArrayHasKey(
            'class',
            $this->reflectionCall($listView, 'getDefaultConfigs')
        );
    }


    public function testSetGetClassAndId()
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
