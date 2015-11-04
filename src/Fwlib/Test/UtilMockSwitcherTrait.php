<?php
namespace Fwlib\Test;

use Fwlib\Util\UtilContainer;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Trait for easy begin/end util mock
 *
 * Notice: You can not duplicate create mock, do not forget to end mock after
 * use, better put in setUpBeforeClass() and tearDownAfterClass().
 *
 * @method  MockBuilder getMockBuilder($className)
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait UtilMockSwitcherTrait
{
    /**
     * @var object[]
     */
    protected $originalUtils = [];


    /**
     * @param   string $utilName
     * @param   array  $methods
     * @return  MockObject|object
     */
    protected function beginUtilMock($utilName, $methods = [])
    {
        $utilContainer = UtilContainer::getInstance();

        $getMethod = "get$utilName";
        $instance = $utilContainer->$getMethod();
        $this->originalUtils[$utilName] = $instance;

        $className = get_class($instance);
        $mock = $this->getMockBuilder($className)
            ->setMethods($methods)
            ->getMock();

        $utilContainer->register($utilName, $mock);

        return $mock;
    }


    /**
     * @param   string $utilName
     */
    protected function endUtilMock($utilName)
    {
        UtilContainer::getInstance()
            ->register($utilName, $this->originalUtils[$utilName]);
    }
}
