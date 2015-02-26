<?php
namespace FwlibTest\Util;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Util\AbstractUtilAware;
use Fwlib\Util\UtilContainer;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractUtilAwareTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | AbstractUtilAware
     */
    public function buildMock()
    {
        $utilAware = $this->getMock(
            'Fwlib\Util\AbstractUtilAware',
            null
        );

        return $utilAware;
    }


    public function testGetUtil()
    {
        $utilAware = $this->buildMock();

        $this->assertInstanceOf(
            'Fwlib\Util\StringUtil',
            $this->reflectionCall($utilAware, 'getUtil', ['String'])
        );
    }


    public function testGetUtilContainer()
    {
        $utilAware = $this->buildMock();

        $this->assertInstanceOf(
            'Fwlib\Util\UtilContainerInterface',
            $utilAware->getUtilContainer()
        );
    }


    public function testSetUtilContainer()
    {
        $utilAware = $this->buildMock();

        $utilAware->setUtilContainer(UtilContainer::getInstance());
        $this->assertInstanceOf(
            'Fwlib\Util\UtilContainerInterface',
            $utilAware->getUtilContainer()
        );
    }
}
