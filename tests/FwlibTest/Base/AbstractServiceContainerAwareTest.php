<?php
namespace FwlibTest\Base;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Base\AbstractServiceContainerAware;
use Fwlib\Test\ServiceContainerTest;

/**
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractServiceContainerAwareTest extends PHPunitTestCase
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
            ServiceContainerTest::getInstance()
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
