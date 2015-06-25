<?php
namespace Fwlib\Html\Generator\Helper;

/**
 * Trait of element property, except class and id
 *
 * @see \Fwlib\Html\Generator\ElementInterface
 *
 * @method  mixed   getConfig($key, $default)
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ElementPropertyTrait
{
    /**
     * Comment or description of element
     *
     * Used to help user understand what this element for.
     *
     * @var string
     */
    protected $comment = '';

    /**
     * Name of form input in html output
     *
     * @var string
     */
    protected $name = '';

    /**
     * Tip to help user input valid data
     *
     * Used to tell user how to fill form inputs.
     *
     * @var string
     */
    protected $tip = '';

    /**
     * @var string[]
     */
    protected $validateRules = [];

    /**
     * Value of element
     *
     * @var mixed
     */
    protected $value = null;


    /**
     * @see \Fwlib\Html\Generator\ElementInterface::getComment()
     *
     * @return  string
     */
    public function getComment()
    {
        return $this->comment;
    }


    /**
     * @see \Fwlib\Html\Generator\ElementInterface::getName()
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @see \Fwlib\Html\Generator\ElementInterface::getTip()
     *
     * @return  string
     */
    public function getTip()
    {
        return $this->tip;
    }


    /**
     * @see \Fwlib\Html\Generator\ElementInterface::getValidateRules()
     *
     * @return  \string[]
     */
    public function getValidateRules()
    {
        return $this->validateRules;
    }


    /**
     * @see \Fwlib\Html\Generator\ElementInterface::getValue()
     *
     * Consider default value config if null.
     *
     * @return  mixed
     */
    public function getValue()
    {
        $value = $this->value;
        $defaultValue = $this->getConfig('default', null);

        if (is_null($value) && !is_null($defaultValue)) {
            $value = $defaultValue;
        }

        return $value;
    }


    /**
     * @see \Fwlib\Html\Generator\ElementInterface::setComment()
     *
     * @param   string  $comment
     * @return  static
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }


    /**
     * @see \Fwlib\Html\Generator\ElementInterface::setName()
     *
     * @param   string  $name
     * @return  static
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }


    /**
     * @see \Fwlib\Html\Generator\ElementInterface::setTip()
     *
     * @param   string  $tip
     * @return  static
     */
    public function setTip($tip)
    {
        $this->tip = $tip;

        return $this;
    }


    /**
     * @see \Fwlib\Html\Generator\ElementInterface::setValidateRules()
     *
     * @param   \string[] $validateRules
     * @return  static
     */
    public function setValidateRules($validateRules)
    {
        $this->validateRules = $validateRules;

        return $this;
    }


    /**
     * @see \Fwlib\Html\Generator\ElementInterface::setValue()
     *
     * @param   mixed   $value
     * @return  static
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
