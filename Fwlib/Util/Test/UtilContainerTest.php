<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class UtilContainerTest extends PHPunitTestCase
{
    /**
     * @return  UtilContainer
     */
    protected function buildMock()
    {
        $utilContainer = $this->getMockBuilder(
            'Fwlib\Util\UtilContainer'
        )
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        return $utilContainer;
    }


    public function testGet()
    {
        $utilContainer = $this->buildMock();

        $this->assertEquals(
            42,
            $utilContainer->get('Array')->getIdx(array(), 'foo', 42)
        );

        $this->assertInstanceOf(
            'Fwlib\Util\ArrayUtil',
            $utilContainer->getArray()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\DatetimeUtil',
            $utilContainer->getDatetime()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\Env',
            $utilContainer->getEnv()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\EscapeColor',
            $utilContainer->getEscapeColor()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\FileSystem',
            $utilContainer->getFileSystem()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\HttpUtil',
            $utilContainer->getHttp()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\Ip',
            $utilContainer->getIp()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\Json',
            $utilContainer->getJson()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\McryptSimpleIv',
            $utilContainer->getMcryptSimpleIv()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\NumberUtil',
            $utilContainer->getNumber()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\Rfc2047',
            $utilContainer->getRfc2047()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\StringUtil',
            $utilContainer->getString()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\UuidBase16',
            $utilContainer->getUuidBase16()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\UuidBase36',
            $utilContainer->getUuidBase36()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\UuidBase62',
            $utilContainer->getUuidBase62()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\Algorithm\Iso7064',
            $utilContainer->getIso7064()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\Code\ChnCitizenIdentificationNumber',
            $utilContainer->getChnCin()
        );

        $this->assertInstanceOf(
            'Fwlib\Util\Code\ChnOrganizationCode',
            $utilContainer->getChnOrganizationCode()
        );
    }
}
