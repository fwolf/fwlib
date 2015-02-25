<?php
namespace Fwlib\Util;

use Fwlib\Base\AbstractServiceContainer;

/**
 * Util class container
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UtilContainer extends AbstractServiceContainer implements
    UtilContainerInterface
{
    /**
     * {@inheritdoc}
     *
     * Short key without Util suffix is alias for easy use.
     *
     * @var array
     */
    protected $serviceClass = [
        'Array'             => 'Fwlib\Util\ArrayUtil',
        'ArrayUtil'         => 'Fwlib\Util\ArrayUtil',
        'Datetime'          => 'Fwlib\Util\DatetimeUtil',
        'DatetimeUtil'      => 'Fwlib\Util\DatetimeUtil',
        'Env'               => 'Fwlib\Util\Env',
        'EscapeColor'       => 'Fwlib\Util\EscapeColor',
        'FileSystem'        => 'Fwlib\Util\FileSystem',
        'Http'              => 'Fwlib\Util\HttpUtil',
        'HttpUtil'          => 'Fwlib\Util\HttpUtil',
        'Ip'                => 'Fwlib\Util\Ip',
        'Json'              => 'Fwlib\Util\Json',
        'McryptSimpleIv'    => 'Fwlib\Util\McryptSimpleIv',
        'Number'            => 'Fwlib\Util\NumberUtil',
        'NumberUtil'        => 'Fwlib\Util\NumberUtil',
        'Rfc2047'           => 'Fwlib\Util\Rfc2047',
        'String'            => 'Fwlib\Util\StringUtil',
        'StringUtil'        => 'Fwlib\Util\StringUtil',
        'UuidBase16'        => 'Fwlib\Util\UuidBase16',
        'UuidBase36'        => 'Fwlib\Util\UuidBase36',
        'UuidBase62'        => 'Fwlib\Util\UuidBase62',

        'Iso7064'           => 'Fwlib\Util\Algorithm\Iso7064',

        'ChnCin'                => 'Fwlib\Util\Code\ChnCitizenIdentificationNumber',
        'ChnCinCode'            => 'Fwlib\Util\Code\ChnCitizenIdentificationNumber',
        'ChnOrgCode'            => 'Fwlib\Util\Code\ChnOrganizationCode',
        'ChnOrganizationCode'   => 'Fwlib\Util\Code\ChnOrganizationCode',
    ];


    /**
     * @return  \Fwlib\Util\ArrayUtil
     */
    public function getArray()
    {
        return $this->get('ArrayUtil');
    }


    /**
     * @return  \Fwlib\Util\DatetimeUtil
     */
    public function getDatetime()
    {
        return $this->get('DatetimeUtil');
    }


    /**
     * @return  \Fwlib\Util\Env
     */
    public function getEnv()
    {
        return $this->get('Env');
    }


    /**
     * @return  \Fwlib\Util\EscapeColor
     */
    public function getEscapeColor()
    {
        return $this->get('EscapeColor');
    }


    /**
     * @return  \Fwlib\Util\FileSystem
     */
    public function getFileSystem()
    {
        return $this->get('FileSystem');
    }


    /**
     * @return  \Fwlib\Util\HttpUtil
     */
    public function getHttp()
    {
        return $this->get('HttpUtil');
    }


    /**
     * @return  \Fwlib\Util\Ip
     */
    public function getIp()
    {
        return $this->get('Ip');
    }


    /**
     * @return  \Fwlib\Util\Json
     */
    public function getJson()
    {
        return $this->get('Json');
    }


    /**
     * @return  \Fwlib\Util\McryptSimpleIv
     */
    public function getMcryptSimpleIv()
    {
        return $this->get('McryptSimpleIv');
    }


    /**
     * @return  \Fwlib\Util\NumberUtil
     */
    public function getNumber()
    {
        return $this->get('NumberUtil');
    }


    /**
     * @return  \Fwlib\Util\Rfc2047
     */
    public function getRfc2047()
    {
        return $this->get('Rfc2047');
    }


    /**
     * @return  \Fwlib\Util\StringUtil
     */
    public function getString()
    {
        return $this->get('StringUtil');
    }


    /**
     * @return  \Fwlib\Util\UuidBase16
     */
    public function getUuidBase16()
    {
        return $this->get('UuidBase16');
    }


    /**
     * @return  \Fwlib\Util\UuidBase36
     */
    public function getUuidBase36()
    {
        return $this->get('UuidBase36');
    }


    /**
     * @return  \Fwlib\Util\UuidBase62
     */
    public function getUuidBase62()
    {
        return $this->get('UuidBase62');
    }


    /**
     * @return  \Fwlib\Util\Algorithm\Iso7064
     */
    public function getIso7064()
    {
        return $this->get('Iso7064');
    }


    /**
     * @return  \Fwlib\Util\Code\ChnCitizenIdentificationNumber
     */
    public function getChnCin()
    {
        return $this->get('ChnCin');
    }


    /**
     * @return  \Fwlib\Util\Code\ChnOrganizationCode
     */
    public function getChnOrganizationCode()
    {
        return $this->get('ChnOrganizationCode');
    }


    /**
     * {@inheritdoc}
     *
     * Inject self to Util instance.
     *
     * @param   string  $name
     * @return  mixed
     */
    protected function newService($name)
    {
        $service = parent::newService($name);

        if (method_exists($service, 'setUtilContainer')) {
            $service->setUtilContainer($this);
        }

        return $service;
    }
}
