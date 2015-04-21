<?php
namespace FwlibTest\Test\Benchmark;

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
}
