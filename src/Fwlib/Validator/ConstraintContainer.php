<?php
namespace Fwlib\Validator;

use Fwlib\Base\AbstractServiceContainer;
use Fwlib\Test\TestServiceContainer;
use Fwlib\Util\UtilContainer;

/**
 * Validate constraint container
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ConstraintContainer extends AbstractServiceContainer
{
    /**
     * {@inheritdoc}
     *
     * @var array
     */
    protected $serviceClass = [
        'Email'     => 'Fwlib\Validator\Constraint\Email',
        'Ipv4'      => 'Fwlib\Validator\Constraint\Ipv4',
        'Length'    => 'Fwlib\Validator\Constraint\Length',
        'NotEmpty'  => 'Fwlib\Validator\Constraint\NotEmpty',
        'Required'  => 'Fwlib\Validator\Constraint\Required',
        'Regex'     => 'Fwlib\Validator\Constraint\Regex',
        'Url'       => 'Fwlib\Validator\Constraint\Url',
    ];

    /**
     * @var UtilContainer
     */
    protected $utilContainer = null;


    /**
     * {@inheritdoc}
     *
     * Inject ServiceContainer, UtilContainer to Constraint instance.
     *
     * @param   string  $name
     * @return  AbstractConstraint
     */
    protected function newService($name)
    {
        $service = parent::newService($name);

        // Fix: parent will set ConstraintContainer as ServiceContainer
        // :TODO: Make a service container for Fwlib
        if (method_exists($service, 'setServiceContainer')) {
            $service->setServiceContainer(
                TestServiceContainer::getInstance()
            );
        }

        if (method_exists($service, 'setUtilContainer')) {
            if (is_null($this->utilContainer)) {
                $this->setUtilContainer(null);
            }

            $service->setUtilContainer($this->utilContainer);
        }

        return $service;
    }


    /**
     * Setter of UtilContainer instance
     *
     * @param   UtilContainer   $utilContainer
     * @return  ConstraintContainer
     */
    public function setUtilContainer(UtilContainer $utilContainer = null)
    {
        if (is_null($utilContainer)) {
            $this->utilContainer = UtilContainer::getInstance();
        } else {
            $this->utilContainer = $utilContainer;
        }

        return $this;
    }
}
