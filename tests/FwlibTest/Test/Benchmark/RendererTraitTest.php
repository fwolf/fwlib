<?php
namespace FwlibTest\Test\Benchmark;

use Fwlib\Test\Benchmark\Group;
use Fwlib\Test\Benchmark\Marker;
use Fwlib\Test\Benchmark\RendererTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RendererTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[]    $methods
     * @return  MockObject|RendererTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(RendererTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testAccessors()
    {
        $rendererTrait = $this->buildMock();

        $rendererTrait->setGroups(['foo']);
        $this->assertEqualArray(
            ['foo'],
            $this->reflectionGet($rendererTrait, 'groups')
        );

        $rendererTrait->setMarkers(['markers']);
        $this->assertEqualArray(
            ['markers'],
            $this->reflectionGet($rendererTrait, 'markers')
        );
    }


    public function testAssignColor()
    {
        $colorMap = ['short', 'long'];
        $markers = [0 => [
            0 => (new Marker(0, 0))->setDuration(30),
            1 => (new Marker(0, 1))->setDuration(10),
        ]];
        $markersDump = var_export($markers, true);

        $rendererTrait = $this->buildMock();

        // No color defined
        $this->reflectionSet($rendererTrait, 'colorMap', []);
        $this->reflectionCall($rendererTrait, 'assignColor');
        $this->assertEquals($markersDump, var_export($markers, true));

        $this->reflectionSet($rendererTrait, 'colorMap', $colorMap);

        // No group defined
        $this->reflectionCall($rendererTrait, 'assignColor');
        $this->assertEquals($markersDump, var_export($markers, true));

        $rendererTrait->setGroups([0 => (new Group(0))->setDuration(40)]);

        // No markers defined
        $this->reflectionCall($rendererTrait, 'assignColor');
        $this->assertEquals($markersDump, var_export($markers, true));

        $rendererTrait->setMarkers($markers);


        /** @var Marker $marker0 */
        $marker0 = $markers[0][0];
        /** @var Marker $marker1 */
        $marker1 = $markers[0][1];

        // Normal with a manual set color
        $marker1->setColor('customColor');
        $this->reflectionCall($rendererTrait, 'assignColor');
        $this->assertEquals('long', $marker0->getColor());
        $this->assertEquals(75, $marker0->getPercent());
        $this->assertEquals('customColor', $marker1->getColor());
        $this->assertEquals(25, $marker1->getPercent());
    }


    public function testGetDurationBounds()
    {
        $colorMap = ['short', 'long'];
        $markers = [0 => [
            0 => (new Marker(0, 0))->setDuration(22),
            1 => (new Marker(0, 1))->setDuration(8),
        ]];

        $rendererTrait = $this->buildMock();
        $this->reflectionSet($rendererTrait, 'colorMap', $colorMap);
        $rendererTrait->setGroups([0 => 'Group Dummy'])
            ->setMarkers($markers);

        $bounds = $this->reflectionCall(
            $rendererTrait,
            'getDurationBounds',
            $markers
        );
        $this->assertEquals([8, 15, 22], $bounds);

        // Only 1 marker
        unset($markers[0][0]);
        $bounds = $this->reflectionCall(
            $rendererTrait,
            'getDurationBounds',
            $markers
        );
        $this->assertEquals([8, 12, 16], $bounds);
    }
}
