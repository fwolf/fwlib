<?php
namespace Fwlib\Auth\Test;

use Fwlib\Auth\AbstractAccessControl;
use Fwlib\Bridge\PHPUnitTestCase;

/**
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-19
 */
class AbstractAccessControlTest extends PHPunitTestCase
{
    protected function buildMock()
    {
        $userSession = $this->getMockForAbstractClass(
            'Fwlib\Auth\AbstractUserSession'
        );

        $accessControl = $this->getMockBuilder(
            'Fwlib\Auth\AbstractAccessControl'
        )
        ->setConstructorArgs(array($userSession))
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
