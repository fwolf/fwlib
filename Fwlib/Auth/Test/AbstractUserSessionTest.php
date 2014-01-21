<?php
namespace Fwlib\Auth\Test;

use Fwlib\Auth\AbstractUserSession;
use Fwlib\Bridge\PHPUnitTestCase;

/**
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-18
 */
class AbstractUserSessionTest extends PHPunitTestCase
{
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

        $userSession->__construct();

        return $userSession;
    }


    public function testClear()
    {
        $userSession = $this->buildMock();

        $this->assertFalse($userSession->isLogined());

        $this->reflectionSet($userSession, 'isLogined', true);
        $this->assertTrue($userSession->isLogined());

        $userSession->clear();
        $this->assertFalse($userSession->isLogined());
    }
}
