<?php
namespace Fwlib\Util;

use Fwlib\Base\ServiceContainerTrait;
use Fwlib\Util\Algorithm\Iso7064;
use Fwlib\Util\Algorithm\McryptSimpleIv;
use Fwlib\Util\Algorithm\Rfc2047;
use Fwlib\Util\Code\ChnCitizenIdentificationNumber;
use Fwlib\Util\Code\ChnOrganizationCode;
use Fwlib\Util\Common\ArrayUtil;
use Fwlib\Util\Common\DatetimeUtil;
use Fwlib\Util\Common\Env;
use Fwlib\Util\Common\EscapeColor;
use Fwlib\Util\Common\FileSystem;
use Fwlib\Util\Common\HttpUtil;
use Fwlib\Util\Common\Ip;
use Fwlib\Util\Common\Json;
use Fwlib\Util\Common\NumberUtil;
use Fwlib\Util\Common\ObjectUtil;
use Fwlib\Util\Common\StringUtil;
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
     * {@inheritdoc}
     *
     * Some util class name end with 'Util', is ugly but have to. ArrayUtil
     * can not be renamed to Array, because its reserved word, others need to
     * keep for identify with other common class name, and avoid IDE type hint
     * confusion.
     *
     * But for convenience, all key of util class have no 'Util' suffix,
     * easier to remember, especially for using {@see register()}.
     */
    protected function buildClassMap()
    {
        $classMap = [
            // Algorithm
            'Iso7064'             => Iso7064::class,
            'McryptSimpleIv'      => McryptSimpleIv::class,
            'Rfc2047'             => Rfc2047::class,
            // Code
            'ChnCin'              => ChnCitizenIdentificationNumber::class,
            'ChnOrganizationCode' => ChnOrganizationCode::class,
            // Common
            'Array'               => ArrayUtil::class,
            'Datetime'            => DatetimeUtil::class,
            'Env'                 => Env::class,
            'EscapeColor'         => EscapeColor::class,
            'FileSystem'          => FileSystem::class,
            'Http'                => HttpUtil::class,
            'Ip'                  => Ip::class,
            'Json'                => Json::class,
            'Number'              => NumberUtil::class,
            'Object'              => ObjectUtil::class,
            'String'              => StringUtil::class,
            // Uuid
            'UuidBase16'          => Base16::class,
            'UuidBase36'          => Base36::class,
            'UuidBase36Short'     => Base36Short::class,
            'UuidBase62'          => Base62::class,
        ];

        return $classMap;
    }


    /**
     * @return  ArrayUtil
     */
    public function getArray()
    {
        return $this->get('Array');
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
        return $this->get('Datetime');
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
        return $this->get('Http');
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
        return $this->get('Number');
    }


    /**
     * @return  ObjectUtil
     */
    public function getObject()
    {
        return $this->get('Object');
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
        return $this->get('String');
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
