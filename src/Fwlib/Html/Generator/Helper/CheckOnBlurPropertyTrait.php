<?php
namespace Fwlib\Html\Generator\Helper;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait CheckOnBlurPropertyTrait
{
    /**
     * Do check when input blur, will NOT overwrite element config
     *
     * @var boolean
     */
    protected $checkOnBlur = false;


    /**
     * @see \Fwlib\Html\Generator\Helper\CheckOnKeyupPropertyInterface::isCheckOnBlur()
     *
     * @return  boolean
     */
    public function isCheckOnBlur()
    {
        return $this->checkOnBlur;
    }


    /**
     * @see \Fwlib\Html\Generator\Helper\CheckOnKeyupPropertyInterface::isCheckOnBlur()
     *
     * @param   boolean $checkOnBlur
     * @return  $this
     */
    public function setCheckOnBlur($checkOnBlur)
    {
        $this->checkOnBlur = $checkOnBlur;

        return $this;
    }
}
