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
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return json_decode($json, $assoc, $depth, $option);
        } else {
            return json_decode($json, $assoc, $depth);
        }
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
     * For PHP 5.3.0+, this method is useless, should use build-in function.
     *
     * @codeCoverageIgnore
     *
     * @param   mixed   $val
     * @param   int      $option     Use if only some of HEX option needed
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

        $jsonStr = '';
        if (0 <= version_compare(PHP_VERSION, '5.3.0')) {
            $jsonStr = json_encode($val, $option);
        } else {
            // Json treat list/array in different way([] vs {}).
            if (is_array($val) || is_object($val)) {
                $isList = is_array($val) && (empty($val)
                    || array_keys($val) === range(0, count($val) - 1));

                if ($isList) {
                    $art = array();
                    foreach ($val as $v) {
                        $art[] = $this->jsonEncodeHex($v, $option);
                    }
                    $jsonStr = '[' . implode(',', $art) . ']';
                } else {
                    $art = array();
                    foreach ($val as $k => $v) {
                        $art[] = $this->jsonEncodeHex($k, $option)
                            . ':'
                            . $this->jsonEncodeHex($v, $option);
                    }
                    $jsonStr = '{' . implode(',', $art) . '}';
                }
            } elseif (is_string($val)) {
                // Manual replace chars
                $jsonStr = json_encode($val);
                $jsonStr = substr($jsonStr, 1);
                $jsonStr = substr($jsonStr, 0, strlen($jsonStr) - 1);

                $jsonStr = $this->replaceByHexOption($jsonStr, $option);
                $jsonStr = '"' . $jsonStr . '"';
            } else {
                // Int, floats, bools, null
                $jsonStr = '"' . json_encode($val) . '"';
            }
        }
        return $jsonStr;
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
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return json_encode($val, $option | JSON_UNESCAPED_UNICODE);
        } else {
            $val = json_encode($val, $option);
            // Double quote '"'(0x22) can't replace back bcs json uses it
            $val = preg_replace(
                '/\\\u((?!(0022))[0-9a-f]{4})/ie',
                "mb_convert_encoding(pack('H4', '\\1'), 'UTF-8', 'UCS-2BE')",
                $val
            );

            // Restore JSON_HEX_* option if used
            $val = $this->replaceByHexOption($val, $option);

            return $val;

            /*
             * Another way is use urlencode before json_encode,
             * and use urldecode after it.
             * But this way can't deal with array recursive,
             * or array have chinese char in it.
             *
             * mb_convert_encoding('&#37257;&#29233;', 'UTF-8', 'HTML-ENTITIES');
             * Need convert \uxxxx to &#xxxxx first.
             */
        }
    }


    /**
     * Do replace same as JSON_HEX_* option
     *
     * @param   string  $val
     * @pram    int     $option
     * @return  string
     */
    protected function replaceByHexOption($val, $option = 0)
    {
        $search = array();
        $replace = array();
        if ($option & JSON_HEX_TAG) {
            $search = array_merge($search, array('<', '>'));
            $replace = array_merge($replace, array('\u003C', '\u003E'));
        }
        if ($option & JSON_HEX_APOS) {
            $search = array_merge($search, array('\''));
            $replace = array_merge($replace, array('\u0027'));
        }
        if ($option & JSON_HEX_QUOT) {
            $search = array_merge($search, array('\"'));
            $replace = array_merge($replace, array('\u0022'));
        }
        if ($option & JSON_HEX_AMP) {
            $search = array_merge($search, array('&'));
            $replace = array_merge($replace, array('\u0026'));
        }

        $val = str_replace($search, $replace, $val);

        return $val;
    }
}
