<?php
namespace FwlibTest\Bridge;

use Fwlib\Bridge\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class PHPUnitTestCaseTest extends PHPunitTestCase
{
    public function testAssertEqualArray()
    {
        $x = null;
        $y = null;
        $this->assertEqualArray($x, $y);

        $x = array();
        $y = array();
        $this->assertEqualArray($x, $y);

        $x = array(1);
        $y = array(1);
        $this->assertEqualArray($x, $y);
    }


    public function testReflection()
    {
        $dummy = new PHPUnitTestCaseDummy;

        $this->assertEquals(
            42,
            $this->reflectionGet($dummy, 'privateProperty')
        );

        $i = $this->reflectionCall(
            $dummy,
            'protectedMethod',
            array(4, 2)
        );
        $this->assertEquals(42, $i);

        $i = $this->reflectionCall(
            $dummy,
            'protectedMethodWithoutParameter'
        );
        $this->assertEquals(42, $i);
    }
}
