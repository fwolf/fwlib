<?php
namespace Fwlib\Util;

/**
 * Class/Object relate utils
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ObjectUtil
{
    /**
     * Get class name without namespace from full qualified class name
     *
     * @see http://php.net/manual/en/function.get-class.php#107964
     *
     * @param   string  $fullName
     * @return  string
     */
    public function getClassName($fullName)
    {
        return implode('', array_slice(explode('\\', $fullName), -1));
    }


    /**
     * Get namespace from full qualified class name
     *
     * @see http://php.net/manual/en/function.get-class.php#107964
     *
     * @param   string  $fullName
     * @return  string
     */
    public function getNamespace($fullName)
    {
        return implode(
            '\\',
            array_slice(explode('\\', $fullName), 0, -1)
        );
    }
}
