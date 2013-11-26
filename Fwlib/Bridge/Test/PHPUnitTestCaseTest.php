<?php
namespace Fwlib\Bridge\Test;

use Fwlib\Bridge\PHPUnitTestCase;

/**
 * Test for Fwlib\Bridge\PHPUnitTestCase
 *
 * @package     Fwlib\Bridge\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-26
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
}
