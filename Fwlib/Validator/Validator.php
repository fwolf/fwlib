<?php
namespace Fwlib\Validator;


/**
 * Validate data and got fail message
 *
 * @package     Fwlib\Validator
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-03
 */
class Validator
{
    /**
     * Constraint instances
     *
     * Instance are stored here for reuse.
     *
     * @var array
     */
    protected $constraintInstance = array();

    /**
     * Map of constraint to its implemet class name
     *
     * @var array
     */
    protected $constraintMap = array(
        'email'     => 'Fwlib\Validator\Constraint\Email',
        'ipv4'      => 'Fwlib\Validator\Constraint\Ipv4',
        'length'    => 'Fwlib\Validator\Constraint\Length',
        'notEmpty'  => 'Fwlib\Validator\Constraint\NotEmpty',
        'required'  => 'Fwlib\Validator\Constraint\Required',
    );

    /**
     * Validate fail message
     *
     * @var array
     */
    protected $message = array();


    /**
     * Get constraint instance
     *
     * @param   string  $name
     * @return  Fwlib\Validator\ConstraintInterface
     */
    protected function getConstraint($name)
    {
        if (!isset($this->constraintMap[$name])) {
            throw new \Exception("Constraint $name not registed");
        }

        if (isset($this->constraintInstance[$name])) {
            return $this->constraintInstance[$name];

        } else {
            $class = $this->constraintMap[$name];
            $instance = new $class;
            $this->constraintInstance[$name] = $instance;
            return $instance;
        }
    }


    /**
     * Get last validate fail message
     *
     * Return empty when validate success.
     *
     * @return  array
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * Register a new constraint
     *
     * @param   string  $constraintName
     * @param   string  $className
     */
    public function registerConstraint($constraintName, $className)
    {
        $this->constraintMap[$constraintName] = $className;
    }


    /**
     * Do validate
     *
     * $rule include constraunt name and data(optional), with format
     * 'constraintName[: constraintData]'. constraintData is needed for some
     * constraint, eg: an 'length' constraint need a value to compare with,
     * the rule string is like 'length: 42', the '42' is $constraintData.
     *
     * $rule can be array of rules.
     *
     * @param   mixed   $value
     * @param   mixed   $rule
     * @return  boolean
     */
    public function validate($value, $rule = null)
    {
        // Clear previous message
        $this->message = array();


        $valid = true;
        foreach ((array)$rule as $ruleString) {
            // Detect if ruleString include constraintData
            $ruleString = trim($ruleString);
            $i = preg_match('/^([\w\d]+):/', $ruleString, $match);

            if (0 == $i) {
                $constraintName = $ruleString;
                $constraintData = null;

            } else {
                $constraintName = $match[1];
                $constraintData = trim(
                    substr($ruleString, strlen($constraintName) + 1)
                );
            }

            $constraint = $this->getConstraint($constraintName);

            if (!$constraint->validate($value, $constraintData)) {
                $valid = false;
                $this->message = array_merge(
                    $this->message,
                    $constraint->getMessage()
                );
            }
        }

        return $valid;
    }
}
