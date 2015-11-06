<?php
namespace Fwlib\Web;

use Fwlib\Base\SingletonTrait;
use Fwlib\Util\UtilContainer;

/**
 * Request content holder
 *
 * Content are cached as property. They can also be modified via setter, to
 * implement default module/action etc.
 *
 * @see         \Fwlib\Web\RequestInterface
 *
 * @property    string $actionParameter
 * @property    string $moduleParameter
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait RequestTrait
{
    use SingletonTrait;


    /**
     * @var string
     */
    protected $action = null;

    /**
     * @var string
     */
    protected $module = null;


    /**
     * @see RequestInterface::getAction()
     *
     * @return  string
     */
    public function getAction()
    {
        if (is_null($this->action)) {
            $this->action = UtilContainer::getInstance()->getHttp()
                ->getGet($this->actionParameter);
        }

        return $this->action;
    }


    /**
     * @see RequestInterface::getModule()
     *
     * @return  string
     */
    public function getModule()
    {
        if (is_null($this->module)) {
            $this->module = UtilContainer::getInstance()->getHttp()
                ->getGet($this->moduleParameter);
        }

        return $this->module;
    }
}
