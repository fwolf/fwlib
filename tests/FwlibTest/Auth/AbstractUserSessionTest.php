<?php
namespace FwlibTest\Auth;

use Fwlib\Auth\AbstractUserSession;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractUserSessionTest extends PHPUnitTestCase
{
    /**
     * @return AbstractUserSession
     */
    protected function buildMock()
    {
        $userSession = $this->getMockBuilder(
            'Fwlib\Auth\AbstractUserSession'
        )
        ->setMethods(['__construct', 'load', 'save'])
        ->getMockForAbstractClass();

        $userSession->expects($this->once())
            ->method('load');

        $userSession->expects($this->never())
            ->method('save');

        /** @type AbstractUserSession $userSession */
        $userSession->__construct();

        return $userSession;
    }


    public function testClear()
    {
        $userSession = $this->buildMock();

        $this->assertFalse($userSession->isLoggedIn());

        $this->reflectionSet($userSession, 'isLoggedIn', true);
        $this->assertTrue($userSession->isLoggedIn());

        $userSession->clear();
        $this->assertFalse($userSession->isLoggedIn());
    }
}
