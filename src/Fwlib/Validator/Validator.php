<?php
namespace Fwlib\Validator;

use Fwlib\Base\Exception\ServiceInstanceCreationFailException;

/**
 * Validate data and got fail message
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Validator
{
    use ConstraintContainerAwareTrait;


    /**
     * Validate fail message
     *
     * @var array
     */
    protected $message = [];


    /**
     * Constructor
     *
     * @param   ConstraintContainerInterface $constraintContainer
     */
    public function __construct(
        ConstraintContainerInterface $constraintContainer = null
    ) {
        $this->constraintContainer = $constraintContainer;
    }


    /**
     * Get constraint instance
     *
     * @param   string  $name
     * @return  ConstraintInterface
     * @throws  ServiceInstanceCreationFailException
     */
    protected function getConstraint($name)
    {
        $constraintContainer = $this->getConstraintContainer();

        $method = "get{$name}";

        if (method_exists($constraintContainer, $method)) {
            return $constraintContainer->$method();

        } else {
            throw (new ServiceInstanceCreationFailException)
                ->setServiceName($name);
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
     * Do validate
     *
     * $rule include constraint name and data(optional), with format
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
        $this->message = [];


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

            $constraint = $this->getConstraint(ucfirst($constraintName));

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
