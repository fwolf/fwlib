<?php
namespace Fwlib\Validator;

use Fwlib\Base\AbstractServiceContainer;
use Fwlib\Validator\ConstraintContainer;

/**
 * Validate data and got fail message
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Validator
{
    /**
     * @var ConstraintContainer
     */
    protected $constraintContainer = null;

    /**
     * Validate fail message
     *
     * @var array
     */
    protected $message = [];


    /**
     * Constructor
     *
     * @param   ConstraintContainer $constraintContainer
     */
    public function __construct(
        ConstraintContainer $constraintContainer = null
    ) {
        $this->constraintContainer = $constraintContainer;
    }


    /**
     * Get constraint instance
     *
     * @param   string  $name
     * @return  Fwlib\Validator\ConstraintInterface
     */
    protected function getConstraint($name)
    {
        if (is_null($this->constraintContainer)) {
            $this->setConstraintContainer(null);
        }

        return $this->constraintContainer->get($name);
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
     * Set constraint container instance
     *
     * @param   ConstraintContainer $constraintContainer
     * @return  Validator
     */
    public function setConstraintContainer($constraintContainer = null)
    {
        if (is_null($constraintContainer)) {
            $this->constraintContainer = ConstraintContainer::getInstance();
        } else {
            $this->ConstraintContainer = $constraintContainer;
        }

        return $this;
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
