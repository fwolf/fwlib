<?php
namespace FwlibTest\Base;

use FwlibTest\Aide\TestServiceContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractServiceContainerAwareTest extends PHPUnitTestCase
{
    protected function buildMock()
    {
        $serviceContainerAware = $this->getMock(
            'Fwlib\Base\AbstractServiceContainerAware',
            null
        );

        return $serviceContainerAware;
    }


    public function testGetServiceWithAutoSet()
    {
        $serviceContainerAware = $this->buildMock();

        $this->assertInstanceOf(
            'Fwlib\Util\UtilContainerInterface',
            $this->reflectionCall(
                $serviceContainerAware,
                'getService',
                ['UtilContainer']
            )
        );
    }


    public function testGetServiceWithManualSet()
    {
        $serviceContainerAware = $this->buildMock();

        $serviceContainerAware->setServiceContainer(
            TestServiceContainer::getInstance()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\UtilContainerInterface',
            $this->reflectionCall(
                $serviceContainerAware,
                'getService',
                ['UtilContainer']
            )
        );
    }
}
