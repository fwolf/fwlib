<?php
namespace Fwlib\Util;

use Fwlib\Base\ServiceContainerTrait;
use Fwlib\Util\Algorithm\Iso7064;
use Fwlib\Util\Code\ChnCitizenIdentificationNumber;
use Fwlib\Util\Code\ChnOrganizationCode;
use Fwlib\Util\Uuid\Base16;
use Fwlib\Util\Uuid\Base36;
use Fwlib\Util\Uuid\Base36Short;
use Fwlib\Util\Uuid\Base62;

/**
 * Util class container
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UtilContainer implements UtilContainerInterface
{
    use ServiceContainerTrait;


    /**
     * @return  ArrayUtil
     */
    public function getArray()
    {
        return $this->get('ArrayUtil');
    }


    /**
     * @return  ChnCitizenIdentificationNumber
     */
    public function getChnCin()
    {
        return $this->get('ChnCin');
    }


    /**
     * @return  ChnOrganizationCode
     */
    public function getChnOrganizationCode()
    {
        return $this->get('ChnOrganizationCode');
    }


    /**
     * @return  DatetimeUtil
     */
    public function getDatetime()
    {
        return $this->get('DatetimeUtil');
    }


    /**
     * @return  Env
     */
    public function getEnv()
    {
        return $this->get('Env');
    }


    /**
     * @return  EscapeColor
     */
    public function getEscapeColor()
    {
        return $this->get('EscapeColor');
    }


    /**
     * @return  FileSystem
     */
    public function getFileSystem()
    {
        return $this->get('FileSystem');
    }


    /**
     * @return  HttpUtil
     */
    public function getHttp()
    {
        return $this->get('HttpUtil');
    }


    /**
     * {@inheritdoc}
     */
    protected function getInitialServiceClassMap()
    {
        $classMap = [
            'ArrayUtil'         => ArrayUtil::class,
            'DatetimeUtil'      => DatetimeUtil::class,
            'Env'               => Env::class,
            'EscapeColor'       => EscapeColor::class,
            'FileSystem'        => FileSystem::class,
            'HttpUtil'          => HttpUtil::class,
            'Ip'                => Ip::class,
            'Json'              => Json::class,
            'McryptSimpleIv'    => McryptSimpleIv::class,
            'NumberUtil'        => NumberUtil::class,
            'Rfc2047'           => Rfc2047::class,
            'StringUtil'        => StringUtil::class,
            'UuidBase16'        => Base16::class,
            'UuidBase36'        => Base36::class,
            'UuidBase36Short'   => Base36Short::class,
            'UuidBase62'        => Base62::class,

            'Iso7064'           => Iso7064::class,

            'ChnCin'                => ChnCitizenIdentificationNumber::class,
            'ChnOrganizationCode'   => ChnOrganizationCode::class,
        ];

        return $classMap;
    }


    /**
     * @return  Ip
     */
    public function getIp()
    {
        return $this->get('Ip');
    }


    /**
     * @return  Iso7064
     */
    public function getIso7064()
    {
        return $this->get('Iso7064');
    }


    /**
     * @return  Json
     */
    public function getJson()
    {
        return $this->get('Json');
    }


    /**
     * @return  McryptSimpleIv
     */
    public function getMcryptSimpleIv()
    {
        return $this->get('McryptSimpleIv');
    }


    /**
     * @return  NumberUtil
     */
    public function getNumber()
    {
        return $this->get('NumberUtil');
    }


    /**
     * @return  Rfc2047
     */
    public function getRfc2047()
    {
        return $this->get('Rfc2047');
    }


    /**
     * @return  StringUtil
     */
    public function getString()
    {
        return $this->get('StringUtil');
    }


    /**
     * @return  Base16
     */
    public function getUuidBase16()
    {
        return $this->get('UuidBase16');
    }


    /**
     * @return  Base36
     */
    public function getUuidBase36()
    {
        return $this->get('UuidBase36');
    }


    /**
     * @return  Base36Short
     */
    public function getUuidBase36Short()
    {
        return $this->get('UuidBase36Short');
    }


    /**
     * @return  Base62
     */
    public function getUuidBase62()
    {
        return $this->get('UuidBase62');
    }
}
