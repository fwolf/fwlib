<?php
namespace Fwlib\Validator;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ConstraintContainerAwareTrait
{
    /**
     * @var ConstraintContainerInterface
     */
    protected $constraintContainer = null;


    /**
     * @return  ConstraintContainer
     */
    protected function getConstraintContainer()
    {
        return is_null($this->constraintContainer)
            ? ConstraintContainer::getInstance()
            : $this->constraintContainer;
    }


    /**
     * Set constraint container instance
     *
     * @param   ConstraintContainerInterface $constraintContainer
     * @return  static
     */
    public function setConstraintContainer(
        ConstraintContainerInterface $constraintContainer
    ) {
        $this->constraintContainer = $constraintContainer;

        return $this;
    }
}
