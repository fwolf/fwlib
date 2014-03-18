<?php
namespace Fwlib\Base\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Base\AbstractServiceContainerAware;
use Fwlib\Test\ServiceContainerTest;

/**
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-03-18
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
                array('UtilContainer')
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
                array('UtilContainer')
            )
        );
    }
}
