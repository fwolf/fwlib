<?php
namespace Fwlib\Util;

use Fwlib\Util\UtilAwareInterface;
use Fwlib\Util\UtilContainer;

/**
 * Class uses Util
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-19
 */
class AbstractUtilAware implements UtilAwareInterface
{
    /**
     * @var UtilContainer
     */
    protected $utilContainer = null;


    /**
     * {@inheritdoc}
     *
     * @param   UtilContainer   $utilContainer
     * @return  AbstractUtilAware
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
