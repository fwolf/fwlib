<?php
namespace Fwlib\Validator\Helper;

use Fwlib\Config\StringOptions;

/**
 * Trait of field and options property
 *
 * Field and options are data part of rule, will be read in constraint.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait FieldAndOptionsPropertyTrait
{
    /**
     * Field, supplement part of rule type.
     *
     * @var string
     */
    protected $field = '';

    /**
     * @var StringOptions
     */
    protected $optionsInstance = null;


    /**
     * @return  string
     */
    public function getField()
    {
        return $this->field;
    }


    /**
     * @param   string  $key
     * @param   mixed   $default
     * @return  string|int|null
     */
    public function getOption($key, $default = false)
    {
        return is_null($this->optionsInstance) ? $default
            : $this->optionsInstance->get($key, $default);
    }


    /**
     * Get all options
     *
     * @return  array
     */
    public function getOptions()
    {
        return is_null($this->optionsInstance) ? []
            : $this->optionsInstance->getAll();
    }


    /**
     * Getter of $options
     *
     * @return  StringOptions
     */
    public function getOptionsInstance()
    {
        return $this->optionsInstance;
    }


    /**
     * Setter of $field
     *
     * @param   string $field
     * @return  static
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }


    /**
     * Setter of $optionsInstance
     *
     * @param   StringOptions $optionsInstance
     * @return  static
     */
    public function setOptionsInstance($optionsInstance)
    {
        $this->optionsInstance = $optionsInstance;

        return $this;
    }
}
