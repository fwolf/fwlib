<?php
namespace Fwlib\Validator;


/**
 * Interface for validate constraint
 *
 * @package     Fwlib\Validator
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-03
 */
interface ConstraintInterface
{
    /**
     * Get last validate fail message array
     *
     * @return  array
     */
    public function getMessage();


    /**
     * Do validate on value
     *
     * Validate pass/success will return true, otherwise return false.
     *
     * $ruleData data part in rule string after 'constraintName:', is needed
     * data for some constraint, eg: an 'equal' constraint need a value to
     * compare with, the rule string is like 'length: 42', the 42 is
     * $ruleData.
     *
     * @param   mixed   $value
     * @param   string  $ruleData
     * @return  boolean
     */
    public function validate($value, $ruleData = null);
}
