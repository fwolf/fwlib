<?php
namespace Fwlib\Util;

use Fwlib\Base\AbstractServiceContainer;

/**
 * Util class container
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-18
 */
class UtilContainer extends AbstractServiceContainer
{
    /**
     * {@inheritdoc}
     *
     * Short key without Util suffix is alias for easy use.
     *
     * @var array
     */
    protected $serviceClass = array(
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
        'String'            => 'Fwlib\Util\String',
        'StringUtil'        => 'Fwlib\Util\StringUtil',
        'UuidBase16'        => 'Fwlib\Util\UuidBase16',
        'UuidBase36'        => 'Fwlib\Util\UuidBase36',
        'UuidBase62'        => 'Fwlib\Util\UuidBase62',

        'Iso7064'           => 'Fwlib\Util\Algorithm\Iso7064',

        'ChnCin'                => 'Fwlib\Util\Code\ChnCitizenIdentificationNumber',
        'ChnCinCode'            => 'Fwlib\Util\Code\ChnCitizenIdentificationNumber',
        'ChnOrgCode'            => 'Fwlib\Util\Code\ChnOrgCode',
        'ChnOrganizationCode'   => 'Fwlib\Util\Code\ChnOrgCode',
    );


    /**
     * {@inheritdoc}
     *
     * Inject self to Util instance.
     *
     * @param   string  $name
     * @return  AbstractUtil
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
