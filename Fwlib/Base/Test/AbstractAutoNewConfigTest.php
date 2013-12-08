<?php
namespace Fwlib\Base\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Base\Test\AbstractAutoNewConfigDummy;

/**
 * Test for Fwlib\Base\AbstractAutoNewConfig
 *
 * @package     Fwlib\Base\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-22
 */
class AbstractAutoNewConfigTest extends PHPunitTestCase
{
    public $dummy;


    public function __construct()
    {
        $this->dummy = new AbstractAutoNewConfigDummy;
    }


    public function testAutoNew()
    {
        $this->assertFalse(isset($this->dummy->returnValue));
        $this->dummy->returnValue;
        $this->assertTrue(isset($this->dummy->returnValue));
    }


    public function testContruct()
    {
        $this->dummy = new AbstractAutoNewConfigDummy(
            array(
                'config.key1'   => 10,
                'config.key2'   => 20,
            )
        );
        $this->assertEquals(20, $this->dummy->getConfig('config.key2'));
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

        $this->assertEquals(null, $this->dummy->foo);
    }


    public function testSetConfig()
    {
        $this->dummy->setConfig('key', 'foo');
        $this->assertEquals('foo', $this->dummy->getConfig('key'));

        // Overwrite exists config
        $this->dummy->setConfig('key', 'bar');
        $this->assertEquals('bar', $this->dummy->getConfig('key'));
    }
}
