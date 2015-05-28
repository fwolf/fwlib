<?php
namespace Fwlib\Validator;

use Fwlib\Base\Exception\ServiceInstanceCreationFailException;
use Fwlib\Config\StringOptions;

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
     * Validate fail messages
     *
     * @var array
     */
    protected $messages = [];


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
    public function getMessages()
    {
        return $this->messages;
    }


    /**
     * Do validate
     *
     * @param   mixed       $value
     * @param   string[]    $rules
     * @return  boolean
     */
    public function validate($value, array $rules = [])
    {
        // Clear previous message
        $this->messages = [];

        $valid = true;
        foreach ($rules as $ruleString) {
            $rule = new Rule($ruleString);

            $type = $rule->getType();
            $constraint = $this->getConstraint(ucfirst($type));
            $constraint->setField($rule->getField())
                ->setOptionsInstance($rule->getOptionsInstance());

            if (!$constraint->validate($value)) {
                $valid = false;
                $this->messages = array_merge(
                    $this->messages,
                    $constraint->getMessages()
                );
            }
        }

        return $valid;
    }
}
