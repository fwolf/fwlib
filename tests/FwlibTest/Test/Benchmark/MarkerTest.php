<?php
namespace FwlibTest\Test\Benchmark;

use Fwlib\Test\Benchmark\Marker;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class MarkerTest extends PHPUnitTestCase
{
    /**
     * @param   string[]    $methods
     * @param   int         $groupId
     * @param   int         $markerId
     * @return  MockObject|Marker
     */
    protected function buildMock(
        array $methods = null,
        $groupId = 0,
        $markerId = 0
    ) {
        $mock = $this->getMock(
            Marker::class,
            $methods,
            [$groupId, $markerId]
        );

        return $mock;
    }


    public function testAccessors()
    {
        $marker = $this->buildMock(null, 24, 42);

        $this->assertEquals(24, $marker->getGroupId());
        $this->assertEquals(42, $marker->getId());

        $marker->setColor('#FFFFFF');
        $this->assertEquals('#FFFFFF', $marker->getColor());

        $marker->setPercent(11.1);
        $this->assertEquals(11.1, $marker->getPercent());
    }
}
