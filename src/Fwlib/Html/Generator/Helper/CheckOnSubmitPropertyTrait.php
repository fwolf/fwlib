<?php
namespace Fwlib\Html\Generator\Helper;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait CheckOnSubmitPropertyTrait
{
    /**
     * Do check before form submit
     *
     * @var boolean
     */
    protected $checkOnSubmit = true;


    /**
     * @return  boolean
     */
    public function isCheckOnSubmit()
    {
        return $this->checkOnSubmit;
    }


    /**
     * @param   boolean $checkOnSubmit
     * @return  static
     */
    public function setCheckOnSubmit($checkOnSubmit)
    {
        $this->checkOnSubmit = $checkOnSubmit;

        return $this;
    }
}
