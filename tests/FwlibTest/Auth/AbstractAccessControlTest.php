<?php
namespace FwlibTest\Auth;

use Fwlib\Auth\AbstractAccessControl;
use Fwlib\Auth\AbstractUserSession;
use Fwlib\Auth\UserSessionInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractAccessControlTest extends PHPUnitTestCase
{
    /**
     * @return  AbstractAccessControl
     */
    protected function buildMock()
    {
        /** @type AbstractUserSession $userSession */
        $userSession = $this->getMockBuilder(
            AbstractUserSession::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $accessControl = $this->getMockBuilder(
            AbstractAccessControl::class
        )
        ->setConstructorArgs([$userSession])
        ->getMockForAbstractClass();

        return $accessControl;
    }


    public function testConstructor()
    {
        $accessControl = $this->buildMock();

        $this->assertInstanceOf(
            UserSessionInterface::class,
            $this->reflectionGet($accessControl, 'userSession')
        );
    }
}
