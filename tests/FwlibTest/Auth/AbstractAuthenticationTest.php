<?php
namespace FwlibTest\Auth;

use Fwlib\Auth\AbstractAuthentication;
use Fwlib\Auth\AbstractUserSession;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractAuthenticationTest extends PHPUnitTestCase
{
    /**
     * @param   AbstractUserSession $userSession
     * @return  AbstractAuthentication
     */
    protected function buildMock($userSession)
    {
        $authentication = $this->getMockBuilder(
            AbstractAuthentication::class
        )
            ->setConstructorArgs([$userSession])
            ->getMockForAbstractClass();

        return $authentication;
    }


    /**
     * @return  AbstractUserSession
     */
    protected function buildMockUserSession()
    {
        $userSession = $this->getMockBuilder(
            AbstractUserSession::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        return $userSession;
    }


    public function testConstructor()
    {
        $authentication = $this->buildMock(null);

        $this->assertNull(
            $this->reflectionGet($authentication, 'userSession')
        );


        $authentication = $this->buildMock($this->buildMockUserSession());

        $this->assertInstanceOf(
            AbstractUserSession::class,
            $this->reflectionGet($authentication, 'userSession')
        );
    }


    public function testGetIdentity()
    {
        $authentication = $this->buildMock(null);

        $this->assertEmpty($authentication->getIdentity());
    }
}
