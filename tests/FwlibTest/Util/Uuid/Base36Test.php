<?php
namespace FwlibTest\Util\Uuid;

use Fwlib\Util\Uuid\Base36;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Base36Test extends PHPUnitTestCase
{
    /**
     * @return MockObject | Base36
     */
    public function buildMock()
    {
        $mock = $this->getMock(
            Base36::class,
            null
        );

        return $mock;
    }


    public function testGenerateTime()
    {
        $generator = $this->buildMock();

        $x = $this->reflectionCall($generator, 'generateTime');
        $this->assertEquals(10, strlen($x));
    }
}
