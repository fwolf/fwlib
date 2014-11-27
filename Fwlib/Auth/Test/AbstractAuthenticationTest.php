<?php
namespace Fwlib\Auth\Test;

use Fwlib\Auth\AbstractAuthentication;
use Fwlib\Auth\AbstractUserSession;
use Fwlib\Bridge\PHPUnitTestCase;

/**
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class AbstractAuthenticationTest extends PHPunitTestCase
{
    private $authentication;


    public function __construct()
    {
        $this->authentication = $this->buildMock(null);
    }


    /**
     * @param   AbstractUserSession $userSession
     * @return  AbstractAuthentication
     */
    protected function buildMock($userSession)
    {
        $authentication = $this->getMockBuilder(
            'Fwlib\Auth\AbstractAuthentication'
        )
        ->setConstructorArgs(array($userSession))
        ->getMockForAbstractClass();

        return $authentication;
    }


    /**
     * @return  AbstractUserSession
     */
    protected function buildMockUserSession()
    {
        $userSession = $this->getMockBuilder(
            'Fwlib\Auth\AbstractUserSession'
        )
        ->getMockForAbstractClass();

        return $userSession;
    }


    public function testConstructor()
    {
        $authentication = $this->authentication;

        $this->assertNull(
            $this->reflectionGet($authentication, 'userSession')
        );


        $authentication = $this->buildMock($this->buildMockUserSession());

        $this->assertInstanceOf(
            'Fwlib\Auth\AbstractUserSession',
            $this->reflectionGet($authentication, 'userSession')
        );
    }


    public function testGetIdentity()
    {
        $authentication = $this->authentication;

        $this->assertEmpty($authentication->getIdentity());
    }
}
