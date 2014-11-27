<?php
namespace Fwlib\Auth\Test;

use Fwlib\Auth\AbstractUserSession;
use Fwlib\Bridge\PHPUnitTestCase;

/**
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class AbstractUserSessionTest extends PHPunitTestCase
{
    /**
     * @return AbstractUserSession
     */
    protected function buildMock()
    {
        $userSession = $this->getMockBuilder(
            'Fwlib\Auth\AbstractUserSession'
        )
        ->setMethods(array('__construct', 'load', 'save'))
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
