<?php
namespace Fwlib\Html\Generator\Helper;

/**
 * Trait of element property, except class and id
 *
 * @see         \Fwlib\Html\Generator\ElementInterface
 *
 * @method  mixed   getConfig($key, $default = null)
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
     * Title/subject/caption of element
     *
     * Although not included directly in element output html, title is useful
     * when generate form, validate message. With title there did not need to
     * define a meta array including name-title map anymore.
     *
     * @var string
     */
    protected $title = '';

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
     * @see \Fwlib\Html\Generator\Helper\ElementPropertyInterface::getComment()
     *
     * @return  string
     */
    public function getComment()
    {
        return $this->comment;
    }


    /**
     * @see \Fwlib\Html\Generator\Helper\ElementPropertyInterface::getName()
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @see \Fwlib\Html\Generator\Helper\ElementPropertyInterface::getTip()
     *
     * @return  string
     */
    public function getTip()
    {
        return $this->tip;
    }


    /**
     * @see \Fwlib\Html\Generator\Helper\ElementPropertyInterface::getTitle()
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @see \Fwlib\Html\Generator\Helper\ElementPropertyInterface::getValidateRules()
     *
     * @return  \string[]
     */
    public function getValidateRules()
    {
        return $this->validateRules;
    }


    /**
     * @see \Fwlib\Html\Generator\Helper\ElementPropertyInterface::getValue()
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
     * @see \Fwlib\Html\Generator\Helper\ElementPropertyInterface::setComment()
     *
     * @param   string $comment
     * @return  $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }


    /**
     * @see \Fwlib\Html\Generator\Helper\ElementPropertyInterface::setName()
     *
     * @param   string $name
     * @return  $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }


    /**
     * @see \Fwlib\Html\Generator\Helper\ElementPropertyInterface::setTip()
     *
     * @param   string $tip
     * @return  $this
     */
    public function setTip($tip)
    {
        $this->tip = $tip;

        return $this;
    }


    /**
     * @see \Fwlib\Html\Generator\Helper\ElementPropertyInterface::setTitle()
     *
     * @param   string $title
     * @return  $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }


    /**
     * @see \Fwlib\Html\Generator\Helper\ElementPropertyInterface::setValidateRules()
     *
     * @param   \string[] $validateRules
     * @return  $this
     */
    public function setValidateRules($validateRules)
    {
        $this->validateRules = $validateRules;

        return $this;
    }


    /**
     * @see \Fwlib\Html\Generator\Helper\ElementPropertyInterface::setValue()
     *
     * @param   mixed $value
     * @return  $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
