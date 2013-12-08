<?php
namespace Fwlib\Base\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Base\ReturnValue;
use Fwlib\Base\Test\AbstractAutoNewConfigDummy;
use Fwlib\Test\ServiceContainerTest;

/**
 * Test for Fwlib\Base\AbstractAutoNewInstance
 *
 * @package     Fwlib\Base\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-22
 */
class AbstractAutoNewInstanceTest extends PHPunitTestCase
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

        /*
        $this->assertFalse(isset($this->dummy->abstractAutoNewConfigDummy));
        $this->dummy->abstractAutoNewConfigDummy;
        $this->assertTrue(isset($this->dummy->abstractAutoNewConfigDummy));
         */
    }


    /**
     * @expectedException           Exception
     * @expectedExceptionMessage    Need valid ServiceContainer.
     */
    public function testCheckServiceContainer()
    {
        $this->dummy->setServiceContainer(
            ServiceContainerTest::getInstance()
        );
        $this->assertTrue($this->dummy->checkServiceContainer());

        $this->dummy->setServiceContainer(null);
        $this->assertFalse($this->dummy->checkServiceContainer(false));

        // Trigger exception
        $this->dummy->checkServiceContainer(true);
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


    public function testSetInstance()
    {
        $dummy = new AbstractAutoNewConfigDummy;

        $this->assertFalse(isset($dummy->returnValue));
        $dummy->setInstance(new ReturnValue);
        $this->assertTrue(isset($dummy->returnValue));

        $this->assertFalse(isset($dummy->abstractAutoNewConfigDummy));
        // Set classname different with object is allowed
        $dummy->setInstance(new ReturnValue, 'AbstractAutoNewConfigDummy');
        $this->assertTrue(isset($dummy->abstractAutoNewConfigDummy));
    }
}
