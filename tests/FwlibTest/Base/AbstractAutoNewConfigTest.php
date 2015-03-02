<?php
namespace FwlibTest\Base;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractAutoNewConfigTest extends PHPUnitTestCase
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


    public function testConstruct()
    {
        $this->dummy = new AbstractAutoNewConfigDummy(
            [
                'config.key1'   => 10,
                'config.key2'   => 20,
            ]
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
        // is caught when trigger_error, following code will not be executed.
        // If not use phpunit exception by setting false, coverage is ok but got
        // error log print, which not pass in strict mode.
        //
        // Solution:
        // After unit test
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
