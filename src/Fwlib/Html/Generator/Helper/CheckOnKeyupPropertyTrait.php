<?php
namespace Fwlib\Html\Generator\Helper;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait CheckOnKeyupPropertyTrait
{
    /**
     * Do check when user input something, will NOT overwrite element config
     *
     * @var boolean
     */
    protected $checkOnKeyup = false;


    /**
     * @return  boolean
     */
    public function isCheckOnKeyup()
    {
        return $this->checkOnKeyup;
    }


    /**
     * @param   boolean $checkOnKeyup
     * @return  static
     */
    public function setCheckOnKeyup($checkOnKeyup)
    {
        $this->checkOnKeyup = $checkOnKeyup;

        return $this;
    }
}
