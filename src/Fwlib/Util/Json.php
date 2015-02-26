<?php
namespace Fwlib\Util;

/**
 * Json class
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Json
{
    /**
     * Dummy decode method using json_decode
     *
     * @codeCoverageIgnore
     *
     * @param   string  $json
     * @param   boolean $assoc
     * @param   int     $depth
     * @param   int     $option
     * @return  mixed
     */
    public function decode($json, $assoc = false, $depth = 512, $option = 0)
    {
        return json_decode($json, $assoc, $depth, $option);
    }


    /**
     * Dummy encode method using json_encode
     *
     * @param   mixed   $val
     * @param   int     $option
     * @return  string
     */
    public function encode($val, $option = 0)
    {
        return json_encode($val, $option);
    }


    /**
     * Encode with all JSON_HEX_(TAG|AMP|APOS|QUOT) options
     *
     * Option JSON_UNESCAPED_UNICODE is NOT included.
     *
     * @codeCoverageIgnore
     *
     * @param   mixed   $val
     * @param   int     $option     Use if only some of HEX option needed
     * @return  string
     */
    public function encodeHex($val, $option = null)
    {
        if (is_null($option)) {
            $option = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
        }

        // Check json extension
        if (!extension_loaded('json')) {
            error_log('Json::encodeHex(): json extension is not loaded.');
            return null;
        }

        if (is_int($val) || is_numeric($val)) {
            return $val;
        }

        return json_encode($val, $option);
    }


    /**
     * Encode with JSON_UNESCAPED_UNICODE option on
     *
     * @codeCoverageIgnore
     *
     * @param   mixed   $val
     * @param   int     $option         Other original json_encode option
     * @return  string
     */
    public function encodeUnicode($val, $option = 0)
    {
        return json_encode($val, $option | JSON_UNESCAPED_UNICODE);
    }
}
