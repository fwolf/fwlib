<?php
namespace FwlibTest\Test\Benchmark;

use Fwlib\Test\Benchmark\Benchmark;
use Fwlib\Test\Benchmark\Group;
use Fwlib\Test\Benchmark\Marker;
use Fwlib\Test\Benchmark\RendererInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class BenchmarkTest extends PHPUnitTestCase
{
    /**
     * @param   string[]    $methods
     * @return  MockObject|Benchmark
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(
            Benchmark::class,
            $methods
        );

        return $mock;
    }


    public function testAutoStop()
    {
        $benchmark = $this->buildMock(['getCurrentGroup', 'stop']);

        $group = (new Group(0))->setBeginTime(42);
        $benchmark->expects($this->once())
            ->method('getCurrentGroup')
            ->willReturn($group);

        $benchmark->expects($this->once())
            ->method('stop');


        $this->reflectionCall($benchmark, 'autoStop');
    }


    public function testDisplay()
    {
        $benchmark = $this->buildMock(['getOutput']);
        $benchmark->expects($this->once())
            ->method('getOutput')
            ->willReturn('dummy output');

        $this->expectOutputString('dummy output');
        $benchmark->display();
    }


    public function testGetCurrentGroup()
    {
        $group = new Group(3);
        $benchmark = $this->buildMock();
        $this->reflectionSet($benchmark, 'groups', [3 => $group]);

        $this->reflectionSet($benchmark, 'groupId', 1);
        $this->assertNull($this->reflectionCall($benchmark, 'getCurrentGroup'));

        $this->reflectionSet($benchmark, 'groupId', 3);
        $this->assertInstanceOf(
            Group::class,
            $this->reflectionCall($benchmark, 'getCurrentGroup')
        );
    }


    public function testGetOutput()
    {
        $benchmark = $this->buildMock(['autoStop']);
        $benchmark->expects($this->once())
            ->method('autoStop');

        $benchmark->getOutput();
    }


    public function testGetTime()
    {
        $benchmark = $this->buildMock();

        $time = $this->reflectionCall($benchmark, 'getTime');
        // Convert float to string with strval() will loose precision
        $timeStr = sprintf('%f', $time);

        $this->assertRegExp('/\d+\.\d{3,}/', $timeStr);
    }


    public function testMark()
    {
        $benchmark = $this->buildMock();

        $benchmark->start();
        $benchmark->mark('m1');
        $benchmark->mark('', 'red');

        $markerId = $this->reflectionGet($benchmark, 'markerId');
        $this->assertEquals(2, $markerId);

        // When stop, marker id is reset
        $benchmark->stop();
        $markerId = $this->reflectionGet($benchmark, 'markerId');
        $this->assertEquals(0, $markerId);

        /** @var Marker[] $markers */
        $markers = $this->reflectionGet($benchmark, 'markers')[0];
        $marker0 = $markers[0];
        $marker1 = $markers[1];
        $this->assertEquals('m1', $marker0->getDescription());
        $this->assertNotEmpty($marker1->getDescription());
        $this->assertEquals('red', $marker1->getColor());
    }


    public function testSetGetRenderer()
    {
        $benchmark = $this->buildMock();

        /** @var MockObject|RendererInterface $renderer */
        $renderer = $this->getMockBuilder(RendererInterface::class)
            ->getMockForAbstractClass();

        $benchmark->setRenderer($renderer);
        $newRenderer = $this->reflectionCall($benchmark, 'getRenderer');

        $this->assertInstanceOf(RendererInterface::class, $newRenderer);
    }
}
