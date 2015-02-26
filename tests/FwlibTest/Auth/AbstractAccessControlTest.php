<?php
namespace FwlibTest\Auth;

use Fwlib\Auth\AbstractAccessControl;
use Fwlib\Auth\AbstractUserSession;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractAccessControlTest extends PHPunitTestCase
{
    /**
     * @return  AbstractAccessControl
     */
    protected function buildMock()
    {
        /** @type AbstractUserSession $userSession */
        $userSession = $this->getMockForAbstractClass(
            'Fwlib\Auth\AbstractUserSession'
        );

        $accessControl = $this->getMockBuilder(
            'Fwlib\Auth\AbstractAccessControl'
        )
        ->setConstructorArgs([$userSession])
        ->getMockForAbstractClass();

        return $accessControl;
    }


    public function testConstructor()
    {
        $accessControl = $this->buildMock();

        $this->assertInstanceOf(
            'Fwlib\Auth\UserSessionInterface',
            $this->reflectionGet($accessControl, 'userSession')
        );
    }
}
