<?php
namespace Fwlib\Html\Helper;

/**
 * @method  string  getClass($suffix)
 * @method  string  getId($suffix)
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ClassAndIdHtmlTrait
{
    /**
     * @param   string $class
     * @return  string
     */
    protected function getClassHtml($class)
    {
        return empty($class) ? '' : " class='{$class}'";
    }


    /**
     * @param   string $idStr
     * @return  string
     */
    protected function getIdHtml($idStr)
    {
        return empty($idStr) ? '' : " id='{$idStr}'";
    }
}
