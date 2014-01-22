<?php
namespace Fwlib\Validator;


/**
 * Interface for validate constraint
 *
 * @copyright   Copyright 2013-2014 Fwolf
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
     * @param   mixed   $value
     * @param   string  $constraintData
     * @return  boolean
     */
    public function validate($value, $constraintData = null);
}
