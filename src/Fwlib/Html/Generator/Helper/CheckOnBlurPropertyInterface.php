<?php
namespace Fwlib\Html\Generator\Helper;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface CheckOnBlurPropertyInterface
{
    /**
     * @return  boolean
     */
    public function isCheckOnBlur();


    /**
     * @param   boolean $checkOnBlur
     * @return  $this
     */
    public function setCheckOnBlur($checkOnBlur);
}
