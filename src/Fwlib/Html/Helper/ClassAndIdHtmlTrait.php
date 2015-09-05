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
     * Get html string including class and id, with same suffix
     *
     * @param   string $suffix
     * @return  string
     */
    protected function getClassAndIdHtml($suffix = '')
    {
        $class = $this->getClass($suffix);
        $idStr = $this->getId($suffix);

        return $this->getClassHtml($class) . $this->getIdHtml($idStr);
    }


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
