<?php
namespace Fwlib\Util;

use Fwlib\Util\UtilAwareInterface;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\UtilContainerInterface;

/**
 * Class uses Util
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-19
 */
class AbstractUtilAware implements UtilAwareInterface
{
    /**
     * @var UtilContainerInterface
     */
    protected $utilContainer = null;


    /**
     * Get util instance
     *
     * Same with Fwlib\Base\AbstractAutoNewInstance::getUtil()
     *
     * @param   string  $name
     * @return  object  Util instance
     */
    protected function getUtil($name)
    {
        if (is_null($this->utilContainer)) {
            $this->setUtilContainer(null);
        }

        return $this->utilContainer->get($name);
    }


    /**
     * {@inheritdoc}
     *
     * @param   UtilContainerInterface  $utilContainer
     * @return  AbstractUtilAware
     */
    public function setUtilContainer(
        UtilContainerInterface $utilContainer = null
    ) {
        if (is_null($utilContainer)) {
            $this->utilContainer = UtilContainer::getInstance();
        } else {
            $this->utilContainer = $utilContainer;
        }

        return $this;
    }
}
