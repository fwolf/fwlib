<?php
namespace Fwlib\Html\Generator\Helper;

/**
 * Trait for element need value to title conversion when output, title
 * class/id is for title convert from value, not element title.
 *
 * @method  string  getClass($suffix = '')
 * @method  string  getId($suffix = '')
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait GetTitleClassAndIdTrait
{
    /**
     * @return string
     */
    protected function getTitleClass()
    {
        $class = $this->getClass();

        $class = empty($class) ? '' : $class . '__title';

        return $class;
    }


    /**
     * Some element have only one title/option, some element have multiple
     * title/option, so value in title id is optional.
     *
     * @param   mixed $value Assign to put value in id
     * @return  string
     */
    protected function getTitleId($value = null)
    {
        $idStr = $this->getId();

        $valueStr = is_null($value) ? '' : "--{$value}";

        $idStr = empty($idStr) ? '' : $idStr . "__title{$valueStr}";

        return $idStr;
    }
}
