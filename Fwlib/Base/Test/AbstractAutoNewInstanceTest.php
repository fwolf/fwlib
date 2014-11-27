<?php
namespace Fwlib\Base\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Base\ReturnValue;
use Fwlib\Base\Test\AbstractAutoNewConfigDummy;
use Fwlib\Test\ServiceContainerTest;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractAutoNewInstanceTest extends PHPunitTestCase
{
    public $dummy;
    protected $serviceContainer;


    public function __construct()
    {
        $this->dummy = new AbstractAutoNewConfigDummy;

        $this->serviceContainer = ServiceContainerTest::getInstance();
    }


    public function buildMock()
    {
        $mock = $this->getMockForAbstractClass(
            'Fwlib\Base\AbstractAutoNewInstance',
            array()
        );

        return $mock;
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


    public function testGetService()
    {
        $mock = $this->buildMock();

        $mock->setServiceContainer($this->serviceContainer);

        $util = $this->reflectionCall($mock, 'getService', array('Util'));

        $this->assertInstanceOf('Fwlib\Util\UtilContainer', $util);
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


    public function testSetUtilContainer()
    {
        $datetimeUtil =
            $this->reflectionCall($this->dummy, 'getUtil', array('Datetime'));
        $this->assertInstanceOf('Fwlib\Util\DatetimeUtil', $datetimeUtil);

        $this->dummy->setUtilContainer(UtilContainer::getInstance());

        $datetimeUtil =
            $this->reflectionCall($this->dummy, 'getUtil', array('Datetime'));
        $this->assertInstanceOf('Fwlib\Util\DatetimeUtil', $datetimeUtil);
    }
}
