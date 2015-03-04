<?php
namespace FwlibTest\Util;

use Fwlib\Util\UtilContainer;
use Fwlib\Util\UtilContainerAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UtilContainerAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | UtilContainerAwareTrait
     */
    protected function buildMock()
    {
        $utilContainerAware = $this->getMockBuilder(
            UtilContainerAwareTrait::class
        )
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMockForTrait();

        return $utilContainerAware;
    }


    public function testGetWithUtilContainerPropertyUnset()
    {
        $utilContainerAware = $this->buildMock();

        $this->reflectionCall($utilContainerAware, 'getUtilContainer');
        $this->assertNull(
            $this->reflectionGet($utilContainerAware, 'utilContainer')
        );
    }


    public function testGetWithUtilContainerPropertySet()
    {
        $utilContainerAware = $this->buildMock();

        $utilContainer = UtilContainer::getInstance();
        $utilContainer->register('FooUtil', new \Exception);

        $utilContainerAware->setUtilContainer($utilContainer);

        $this->assertInstanceOf(
            'Exception',
            $this->reflectionCall(
                $this->reflectionCall(
                    $utilContainerAware,
                    'getUtilContainer'
                ),
                'get',
                ['FooUtil']
            )
        );
    }
}
