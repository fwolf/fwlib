<?php
namespace FwlibTest\Base;

use Fwlib\Bridge\PHPUnitTestCase;
use FwlibTest\Base\AutoNewObjDummy;

/**
 * Test for Fwlib\Base\AutoNewObj
 *
 * @package     FwlibTest\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-22
 */
class AutoNewObjTest extends PHPunitTestCase
{
    public $dummy;


    public function __construct()
    {
        $this->dummy = new AutoNewObjDummy;
    }


    public function testAutoNew()
    {
        $this->assertFalse(isset($this->dummy->foo));
        $this->dummy->foo;
        $this->assertTrue(isset($this->dummy->foo));
    }


    /**
     * Non-exist property got null and trigger error
     *
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testGetNonExistProperty()
    {
        // Problem:
        // If use phpunit exception, coverage will lose because after exception
        // is catched when trigger_error, following code will not be executed.
        // If not use phpunit exception by setting false, coverage is ok but got
        // error log print, which not pass in strict mode.
        //
        // Solution:
        // After funtional test
        // 1. Suppress error print using '@'
        // 2. Remove expectedException annotation

        // Set this to false to allow code continue when exception occur.
        // Should not use if you want to know what error message it is.
        //\PHPUnit_Framework_Error_Notice::$enabled = false;

        // Final solution: use @codeCoverageIgnore

        $this->assertEquals(null, $this->dummy->bar);
    }
}
