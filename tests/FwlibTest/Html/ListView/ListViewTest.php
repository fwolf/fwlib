<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\ListDto;
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


    public function testSetBody()
    {
        $listView = $this->buildMock();

        $listView->setBody([['key' => 'foo'], ['key' => 'bar']], true);
        /** @var ListDto $listDto */
        $listDto = $this->reflectionCall($listView, 'getListDto');
        $this->assertEquals(2, $listDto->getRowCount());
    }
}
