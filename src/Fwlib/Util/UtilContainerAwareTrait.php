<?php
namespace Fwlib\Util;

/**
 * Trait for util container client
 *
 * @see \Fwlib\Base\ServiceContainerAwareTrait
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait UtilContainerAwareTrait
{
    /**
     * @var UtilContainerInterface
     */
    protected $utilContainer = null;


    /**
     * @return  UtilContainer
     */
    protected function getUtilContainer()
    {
        return is_null($this->utilContainer)
            ? UtilContainer::getInstance()
            : $this->utilContainer;
    }


    /**
     * @param   UtilContainerInterface  $utilContainer
     * @return  static
     */
    public function setUtilContainer(
        UtilContainerInterface $utilContainer
    ) {
        $this->utilContainer = $utilContainer;

        return $this;
    }
}
