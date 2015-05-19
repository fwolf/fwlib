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
     * Do validate on value
     *
     * Validate pass/success will return true, otherwise return false.
     *
     * @param   mixed           $value
     * @param   StringOptions   $options    Can use {@see StringOptions}
     * @return  boolean
     */
    public function validate($value, StringOptions $options = null);
}
