<?php
namespace FwlibTest\Util;

use Fwlib\Util\UtilContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UtilContainerTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | UtilContainer
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance();
    }


    public function testGet()
    {
        $utilContainer = $this->buildMock();

        $this->assertEquals(
            42,
            $utilContainer->getArray()->getIdx([], 'foo', 42)
        );

        $classMap = $this->reflectionCall(
            $utilContainer,
            'getInitialServiceClassMap'
        );

        foreach ($classMap as $simpleName => $fullName) {
            $method = "get{$simpleName}";

            $this->assertInstanceOf($fullName, $utilContainer->$method());
        }
    }


    public function testGetInitialServiceClassMap()
    {
        $utilContainer = $this->buildMock();

        $this->assertNotEmpty(
            $this->reflectionCall($utilContainer, 'getInitialServiceClassMap')
        );
    }
}
