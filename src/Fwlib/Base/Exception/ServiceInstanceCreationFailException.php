<?php
namespace Fwlib\Base\Exception;

/**
 * Extension for can not create service instance
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ServiceInstanceCreationFailException extends \Exception
{
    /**
     * @var string
     */
    protected $serviceName = '';


    /**
     * @param   string $name
     * @return  static
     */
    public function setServiceName($name)
    {
        $this->serviceName = $name;

        $this->message = "Failed to create instance of service $name";

        return $this;
    }
}
