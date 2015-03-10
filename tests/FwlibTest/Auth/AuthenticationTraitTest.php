<?php
namespace FwlibTest\Auth;

use Fwlib\Auth\AuthenticationInterface;
use Fwlib\Auth\AuthenticationTrait;
use Fwlib\Auth\UserSessionInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AuthenticationTraitTest extends PHPUnitTestCase
{
    /**
     * @param   UserSessionInterface    $userSession
     * @return  MockObject | AuthenticationInterface
     */
    protected function buildMock($userSession)
    {
        $mock = $this->getMockBuilder(
            AuthenticationTrait::class
        )
            ->getMockForTrait();

        /** @var AuthenticationInterface $mock */
        $mock->setUserSession($userSession);

        return $mock;
    }


    /**
     * @return  MockObject | UserSessionInterface
     */
    protected function buildMockUserSession()
    {
        $userSession = $this->getMockBuilder(
            UserSessionInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        return $userSession;
    }


    public function testSetGetUserSession()
    {
        $authentication = $this->buildMock(null);

        $this->assertNull(
            $authentication->getUserSession()
        );


        $authentication = $this->buildMock($this->buildMockUserSession());

        $this->assertInstanceOf(
            UserSessionInterface::class,
            $authentication->getUserSession()
        );
    }


    public function testGetIdentity()
    {
        $authentication = $this->buildMock(null);

        $this->assertEmpty($authentication->getIdentity());
    }
}
