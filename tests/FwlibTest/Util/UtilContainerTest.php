<?php
namespace FwlibTest\Util;

use Fwlib\Util\Algorithm\Iso7064;
use Fwlib\Util\ArrayUtil;
use Fwlib\Util\Code\ChnCitizenIdentificationNumber;
use Fwlib\Util\Code\ChnOrganizationCode;
use Fwlib\Util\DatetimeUtil;
use Fwlib\Util\Env;
use Fwlib\Util\EscapeColor;
use Fwlib\Util\FileSystem;
use Fwlib\Util\HttpUtil;
use Fwlib\Util\Ip;
use Fwlib\Util\Json;
use Fwlib\Util\McryptSimpleIv;
use Fwlib\Util\NumberUtil;
use Fwlib\Util\Rfc2047;
use Fwlib\Util\StringUtil;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\Uuid\Base16;
use Fwlib\Util\Uuid\Base36;
use Fwlib\Util\Uuid\Base36Short;
use Fwlib\Util\Uuid\Base62;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UtilContainerTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | UtilContainer
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance();
    }


    public function testGet()
    {
        $utilContainer = $this->buildMock();

        $this->assertEquals(
            42,
            $utilContainer->getArray()->getIdx([], 'foo', 42)
        );

        $this->assertInstanceOf(
            ArrayUtil::class,
            $utilContainer->getArray()
        );

        $this->assertInstanceOf(
            DatetimeUtil::class,
            $utilContainer->getDatetime()
        );

        $this->assertInstanceOf(
            Env::class,
            $utilContainer->getEnv()
        );

        $this->assertInstanceOf(
            EscapeColor::class,
            $utilContainer->getEscapeColor()
        );

        $this->assertInstanceOf(
            FileSystem::class,
            $utilContainer->getFileSystem()
        );

        $this->assertInstanceOf(
            HttpUtil::class,
            $utilContainer->getHttp()
        );

        $this->assertInstanceOf(
            Ip::class,
            $utilContainer->getIp()
        );

        $this->assertInstanceOf(
            Json::class,
            $utilContainer->getJson()
        );

        $this->assertInstanceOf(
            McryptSimpleIv::class,
            $utilContainer->getMcryptSimpleIv()
        );

        $this->assertInstanceOf(
            NumberUtil::class,
            $utilContainer->getNumber()
        );

        $this->assertInstanceOf(
            Rfc2047::class,
            $utilContainer->getRfc2047()
        );

        $this->assertInstanceOf(
            StringUtil::class,
            $utilContainer->getString()
        );

        $this->assertInstanceOf(
            Base16::class,
            $utilContainer->getUuidBase16()
        );

        $this->assertInstanceOf(
            Base36::class,
            $utilContainer->getUuidBase36()
        );

        $this->assertInstanceOf(
            Base36Short::class,
            $utilContainer->getUuidBase36Short()
        );

        $this->assertInstanceOf(
            Base62::class,
            $utilContainer->getUuidBase62()
        );

        $this->assertInstanceOf(
            Iso7064::class,
            $utilContainer->getIso7064()
        );

        $this->assertInstanceOf(
            ChnCitizenIdentificationNumber::class,
            $utilContainer->getChnCin()
        );

        $this->assertInstanceOf(
            ChnOrganizationCode::class,
            $utilContainer->getChnOrganizationCode()
        );
    }


    public function testGetInitialServiceClassMap()
    {
        $utilContainer = $this->buildMock();

        $this->assertNotEmpty(
            $this->reflectionCall($utilContainer, 'getInitialServiceClassMap')
        );
    }
}
