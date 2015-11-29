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

        $head = ['Foo', 'Bar'];
        $listDto->setHead($head);
        $this->assertEqualArray($head, $listDto->getHead());

        $body = [['foo' => 'Foo'], ['bar' => 'Bar']];
        $listDto->setBody($body);
        $this->assertEqualArray($body, $listDto->getBody());

        $body = ['foo' => new \stdClass(), 'bar' => new \stdClass()];
        $listDto->setBody($body);
        $this->assertArrayHasKey('foo', $listDto->getBody());

        $listDto->setBody(null);
        $this->assertNull($listDto->getBody());

        $listDto->setBody([]);
        $this->assertEqualArray([], $listDto->getBody());

        $this->assertEquals(-1, $listDto->getRowCount());
        $listDto->setRowCount(42);
        $this->assertEquals(42, $listDto->getRowCount());
    }


    /**
     * @expectedException \Fwlib\Html\ListView\Exception\InvalidBodyException
     */
    public function testSetInvalidBody()
    {
        $listDto = $this->buildMock();

        // Only 1 dimension, need 2 dimension
        $listDto->setBody(['foo' => 'Foo', 'bar' => 'Bar']);
    }
}
