<?php
namespace FwlibTest\Util\Uuid;

use Fwlib\Util\Uuid\Base36Short;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Base36ShortTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | Base36Short
     */
    public function buildMock()
    {
        $mock = $this->getMock(
            Base36Short::class,
            null
        );

        return $mock;
    }


    public function testGenerate()
    {
        $generator = $this->buildMock();

        $uuid = $generator->generate();

        // Default group id
        $this->assertEquals('1', substr($uuid, 10, 1));

        // Custom should not larger than half of 10{8}
        $random = base_convert(substr($uuid, -5), 36, 10);
        $this->assertLessThan(50000000, $random);

        // For coverage
        $this->assertEquals(
            $this->reflectionGet($generator, 'length'),
            strlen($generator->generate('', '', true))
        );
    }
}
