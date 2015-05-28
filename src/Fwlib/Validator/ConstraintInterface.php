<?php
namespace Fwlib\Validator;

use Fwlib\Config\StringOptions;

/**
 * Interface for validate constraint
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ConstraintInterface
{
    /**
     * Get last validate fail message array
     *
     * @return  array
     */
    public function getMessages();


    /**
     * Setter of $field
     *
     * @param   string $field
     * @return  static
     */
    public function setField($field);


    /**
     * Setter of $optionsInstance
     *
     * @param   StringOptions $optionsInstance
     * @return  static
     */
    public function setOptionsInstance($optionsInstance);


    /**
     * Do validate on value
     *
     * Validate pass/success will return true, otherwise return false.
     *
     * @param   mixed           $value
     * @return  boolean
     */
    public function validate($value);
}
