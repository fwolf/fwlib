<?php
namespace Fwlib\Base;


/**
 * Return value object
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-05-03
 */
class Rv
{

    /**
     * Return value info
     *
     * array(
     *  code,       // Normally 0=no error, c>0=info, c<0=error occur
     *  msg,
     *  data,
     * )
     *
     * @var array
     */
    public $info = array(
        'code'  => 0,
        'msg'   => null,
        'data'  => null,
    );


    /**
     * constructor
     *
     * @param   int     $code
     * @param   string  $msg
     * @param   mixed   $data
     */
    public function __construct($code = 0, $msg = null, $data = null)
    {
        $this->info = array(
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data,
        );
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
     * Get error msg
     *
     * @return  string
     */
    public function errorMsg()
    {
        return $this->info['msg'];
    }


    /**
     * Get error no
     *
     * Do NOT use this for error check, use error() instead.
     *
     * @return  int
     */
    public function errorNo()
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
     * Get/set msg
     *
     * @param   string  $msg
     * @param   boolean $forceNull      Force do value assign even is null
     * @return  string
     */
    public function msg($msg = null, $forceNull = false)
    {
        return $this->getSetInfo('msg', $msg, $forceNull);
    }
}
