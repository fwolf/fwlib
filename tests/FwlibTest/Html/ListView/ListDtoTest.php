<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\ListDto;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ListDtoTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ListDto
     */
    protected function buildMock()
    {
        $mock = $this->getMock(ListDto::class, null);

        return $mock;
    }


    public function test()
    {
        $listDto = $this->buildMock();

        $title = ['Foo', 'Bar'];
        $listDto->setTitle($title);
        $this->assertEqualArray($title, $listDto->getTitle());

        $data = [['foo' => 'Foo'], ['bar' => 'Bar']];
        $listDto->setData($data);
        $this->assertEqualArray($data, $listDto->getData());

        $listDto->setData(null);
        $this->assertNull($listDto->getData());

        $listDto->setData([]);
        $this->assertEqualArray([], $listDto->getData());
    }


    /**
     * @expectedException \Fwlib\Html\ListView\Exception\InvalidDataException
     */
    public function testSetInvalidData()
    {
        $listDto = $this->buildMock();

        // Only 1 dimension, need 2 dimension
        $listDto->setData(['foo' => 'Foo', 'bar' => 'Bar']);
    }
}
