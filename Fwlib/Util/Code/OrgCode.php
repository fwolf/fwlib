<?php
namespace Fwlib\Util\Code;

use Fwlib\Util\AbstractUtilAware;

/**
 * Cert org code
 *
 * @package     Fwlib\Util\Code
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-27
 * @link    http://zh.wikisource.org/zh/GB_11714-1997_全国组织机构代码编制规则
 */
class OrgCode extends AbstractUtilAware
{
    /**
     * Generate org code
     *
     * @param   string  $codeBase  8-bit base code
     * @return  string
     */
    public function gen($codeBase = '')
    {
        $codeBase = strtoupper($codeBase);

        if (empty($codeBase)) {
            // Gen random if empty
            $codeBase = $this->utilContainer->get('StringUtil')->random(8, '0A');
        } elseif (8 != strlen($codeBase)) {
            // Length check
            return '';
        } elseif ('' != preg_replace('/[0-9A-Z]/', '', $codeBase)) {
            // Only 0-9 A-Z allowed
            return '';
        }


        // Prepare value table
        $arVal = array();
        // 0-9 to 0-9
        for ($i = 48; $i < 58; $i ++) {
            $arVal[chr($i)] = $i - 48;
        }
        // A-Z to 10-35
        for ($i = 65; $i < 91; $i ++) {
            $arVal[chr($i)] = $i - 55;
        }

        // Weight table
        $arWeight = array(3, 7, 9, 10, 5, 8, 4, 2);

        // Add each digit value after plus it's weight
        $j = 0;
        for ($i = 0; $i <8; $i ++) {
            $j += $arVal[$codeBase{$i}] * $arWeight[$i];
        }

        // Mod by 11
        $j = $j % 11;

        // Minus by 11
        $j = 11 - $j;

        // Return result
        if (10 == $j) {
            return $codeBase . '-X';
        } elseif (11 == $j) {
            return $codeBase . '-0';
        } else {
            return $codeBase . '-' . strval($j);
        }
    }


    /**
     * Validate org code
     *
     * @param   string  $code
     * @return  boolean
     */
    public function validate($code)
    {
        if (10 != strlen($code)) {
            return false;
        }

        if ('-' != $code[8]) {
            return false;
        }

        if ($code != self::gen(substr($code, 0, 8))) {
            return false;
        }

        return true;
    }
}
