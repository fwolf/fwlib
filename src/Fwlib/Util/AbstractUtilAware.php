<?php
namespace Fwlib\Util;

/**
 * Class uses Util
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractUtilAware implements UtilAwareInterface
{
    /**
     * @type    UtilContainerInterface
     */
    protected $utilContainer = null;


    /**
     * Get util instance
     *
     * @deprecated  Use UtilContainer::getXxx() instead
     *
     * @param   string  $name
     * @return  object  Util instance
     */
    protected function getUtil($name)
    {
        return $this->getUtilContainer()->get($name);
    }


    /**
     * {@inheritdoc}
     *
     * @return  UtilContainer
     */
    public function getUtilContainer()
    {
        if (is_null($this->utilContainer)) {
            $this->utilContainer = UtilContainer::getInstance();
        }

        return $this->utilContainer;
    }


    /**
     * {@inheritdoc}
     */
    public function setUtilContainer(
        UtilContainerInterface $utilContainer = null
    ) {
        $this->utilContainer = $utilContainer;

        return $this;
    }
}
