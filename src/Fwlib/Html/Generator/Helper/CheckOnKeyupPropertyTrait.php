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
     * @see \Fwlib\Html\Generator\Helper\CheckOnKeyupPropertyInterface::isCheckOnKeyup()
     *
     * @return  boolean
     */
    public function isCheckOnKeyup()
    {
        return $this->checkOnKeyup;
    }


    /**
     * @see \Fwlib\Html\Generator\Helper\CheckOnKeyupPropertyInterface::setCheckOnKeyup()
     *
     * @param   boolean $checkOnKeyup
     * @return  $this
     */
    public function setCheckOnKeyup($checkOnKeyup)
    {
        $this->checkOnKeyup = $checkOnKeyup;

        return $this;
    }
}
