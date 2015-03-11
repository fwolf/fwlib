<?php
namespace FwlibTest\Auth;

use Fwlib\Auth\SessionHandlerInterface;
use Fwlib\Auth\UserSessionTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UserSessionTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | UserSessionTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(
            UserSessionTrait::class
        )
        ->setMethods(['load', 'save'])
        ->getMockForTrait();

        $mock->expects($this->once())
            ->method('load');

        $mock->expects($this->never())
            ->method('save');

        /** @var UserSessionTrait $mock */
        $mock->setSessionHandler($this->buildSessionHandlerMock());

        $this->reflectionCall($mock, 'initialize');

        return $mock;
    }


    /**
     * @return MockObject | SessionHandlerInterface
     */
    protected function buildSessionHandlerMock()
    {
        $mock = $this->getMock(
            SessionHandlerInterface::class,
            []
        );

        return $mock;
    }


    public function testClearAndIsLoggedIn()
    {
        $userSession = $this->buildMock();

        $this->assertFalse($userSession->isLoggedIn());

        $this->reflectionSet($userSession, 'isLoggedIn', true);
        $this->assertTrue($userSession->isLoggedIn());

        $userSession->clear();
        $this->assertFalse($userSession->isLoggedIn());
    }
}
