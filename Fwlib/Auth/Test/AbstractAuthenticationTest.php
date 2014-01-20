<?php
namespace Fwlib\Auth\Test;

use Fwlib\Auth\AbstractAuthentication;
use Fwlib\Bridge\PHPUnitTestCase;

/**
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-18
 */
class AbstractAuthenticationTest extends PHPunitTestCase
{
    private $authentication;


    public function __construct()
    {
        $this->authentication = $this->buildMock(null);
    }


    protected function buildMock($userSession)
    {
        $authentication = $this->getMockBuilder(
            'Fwlib\Auth\AbstractAuthentication'
        )
        ->setConstructorArgs(array($userSession))
        ->getMockForAbstractClass();

        return $authentication;
    }


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
