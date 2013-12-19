<?php
namespace Fwlib\Base;

use Fwlib\Util\Json;
use Fwlib\Util\AbstractUtilAware;

/**
 * Return value object
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-05-03
 */
class ReturnValue extends AbstractUtilAware
{

    /**
     * Return value info
     *
     * array(
     *  code,       // Normally 0=no error, c>0=info, c<0=error occur
     *  message,
     *  data,
     * )
     *
     * @var array
     */
    public $info = array(
        'code'    => 0,
        'message' => null,
        'data'    => null,
    );


    /**
     * Constructor
     *
     * $code can be int, or a json encoded string.
     *
     * @param   mixed   $code
     * @param   string  $message
     * @param   mixed   $data
     */
    public function __construct($code = 0, $message = '', $data = null)
    {
        $this->setUtilContainer();

        if (is_int($code)) {
            $this->info = array(
                'code'    => $code,
                'message' => $message,
                'data'    => $data,
            );

        } else {
            $this->loadJson($code);
        }
    }


    /**
     * Get/set code
     *
     * @param   int     $code
     * @param   boolean $forceNull      Force do value assign even is null
     * @return  int
     */
    public function code($code = null, $forceNull = false)
    {
        return $this->getSetInfo('code', $code, $forceNull);
    }


    /**
     * Get/set data
     *
     * @param   mixed   $data
     * @param   boolean $forceNull      Force do value assign even is null
     * @return  mixed
     */
    public function data($data = null, $forceNull = false)
    {
        return $this->getSetInfo('data', $data, $forceNull);
    }


    /**
     * Is result means error ?
     *
     * @return  boolean
     */
    public function error()
    {
        return ($this->info['code'] < 0);
    }


    /**
     * Get error message
     *
     * @return  string
     */
    public function errorMessage()
    {
        return $this->info['message'];
    }


    /**
     * Get error no
     *
     * Do NOT use this for error check, use error() instead.
     *
     * @return  int
     */
    public function errorCode()
    {
        return $this->info['code'];
    }


    /**
     * Get return value info array
     *
     * @return  array
     */
    public function getInfo()
    {
        return $this->info;
    }


    /**
     * Get json encoded info string
     *
     * @return  string
     */
    public function getJson()
    {
        return Json::encodeUnicode($this->info);
    }


    /**
     * Get/set info array
     *
     * @param   string  $idx            Should be one of code/msg/data, but no check
     * @param   mixed   $val
     * @param   boolean $b_force        Force do value assign ignore null
     * @return  mixed
     */
    protected function getSetInfo($idx, $val = null, $forceNull = false)
    {
        if (!is_null($val) || ((is_null($val)) && $forceNull)) {
            $this->info[$idx] = $val;
        }

        return $this->info[$idx];
    }


    /**
     * Load info from json encoded string
     *
     * Input json string MUST include code and message, data can be optional.
     *
     * @param   string  $json
     * @return  ReturnValue
     */
    public function loadJson($json)
    {
        $ar = Json::decode($json, true);

        foreach (array('code', 'message') as $v) {
            if (!isset($ar[$v])) {
                throw new \Exception("Json string to load have no $v info");
            }
        }

        $arrayUtil = $this->utilContainer->get('Array');
        $this->info = array(
            'code'    => $ar['code'],
            'message' => $ar['message'],
            'data'    => $arrayUtil->getIdx($ar, 'data', null),
        );

        return $this;
    }


    /**
     * Get/set message
     *
     * @param   string  $message
     * @param   boolean $forceNull      Force do value assign even is null
     * @return  string
     */
    public function message($message = null, $forceNull = false)
    {
        return $this->getSetInfo('message', $message, $forceNull);
    }
}
