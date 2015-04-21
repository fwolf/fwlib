<?php
namespace FwlibTest\Test\Benchmark;

use Fwlib\Test\Benchmark\Group;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class GroupTest extends PHPUnitTestCase
{
    /**
     * @param   string[]    $methods
     * @param   int         $groupId
     * @return  MockObject|Group
     */
    protected function buildMock(array $methods = null, $groupId = 0)
    {
        $mock = $this->getMock(
            Group::class,
            $methods,
            [$groupId]
        );

        return $mock;
    }


    public function testAccessors()
    {
        $group = $this->buildMock(null, 42);

        $this->assertEquals(42, $group->getId());
    }
}
