<?php
namespace FwlibTest\Auth;

use Fwlib\Auth\AccessControllerInterface;
use Fwlib\Auth\AccessControllerTrait;
use Fwlib\Auth\UserSessionInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AccessControllerTraitTest extends PHPUnitTestCase
{
    /**
     * @return  MockObject | AccessControllerInterface
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(
            AccessControllerTrait::class
        )
            ->getMockForTrait();

        return $mock;
    }


    /**
     * @return MockObject | UserSessionInterface
     */
    protected function buildUserSessionMock()
    {
        $mock = $this->getMockBuilder(
            UserSessionInterface::class
        )
            ->getMock();

        return $mock;
    }


    public function testGetSetUserSession()
    {
        $accessController = $this->buildMock();

        $accessController->setUserSession($this->buildUserSessionMock());
        $this->assertInstanceOf(
            UserSessionInterface::class,
            $accessController->getUserSession()
        );
    }
}
