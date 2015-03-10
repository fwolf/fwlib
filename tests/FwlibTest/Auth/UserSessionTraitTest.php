<?php
namespace FwlibTest\Auth;

use Fwlib\Auth\UserSessionTrait;
use Fwlib\Util\HttpUtil;
use Fwlib\Util\UtilContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UserSessionTraitTest extends PHPUnitTestCase
{
    /** @var HttpUtil */
    protected static $httpUtilBackup;


    /**
     * @return UserSessionTrait
     */
    protected function buildMock()
    {
        $userSession = $this->getMockBuilder(
            UserSessionTrait::class
        )
        ->setMethods(['load', 'save'])
        ->getMockForTrait();

        $userSession->expects($this->once())
            ->method('load');

        $userSession->expects($this->never())
            ->method('save');

        $this->reflectionCall($userSession, 'initialize');

        return $userSession;
    }


    public static function setUpBeforeClass()
    {
        $utilContainer = UtilContainer::getInstance();
        self::$httpUtilBackup = $utilContainer->getHttp();

        $testCase = new self;
        $httpUtil = $testCase->getMock(HttpUtil::class, ['startSession']);
        $utilContainer->register('HttpUtil', $httpUtil);
    }


    public static function tearDownAfterClass()
    {
        UtilContainer::getInstance()
            ->register('HttpUtil', self::$httpUtilBackup);
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
