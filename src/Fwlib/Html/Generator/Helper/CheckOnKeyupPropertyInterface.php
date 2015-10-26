<?php
namespace Fwlib\Html\Generator\Helper;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface CheckOnKeyupPropertyInterface
{
    /**
     * @return  boolean
     */
    public function isCheckOnKeyup();


    /**
     * @param   boolean $checkOnKeyup
     * @return  $this
     */
    public function setCheckOnKeyup($checkOnKeyup);
}
