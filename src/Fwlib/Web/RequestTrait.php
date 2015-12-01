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
     * @see RequestInterface::getActionParameter()
     *
     * @return  string
     */
    public function getActionParameter()
    {
        return $this->actionParameter;
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


    /**
     * @see RequestInterface::getModuleParameter()
     *
     * @return  string
     */
    public function getModuleParameter()
    {
        return $this->moduleParameter;
    }


    /**
     * @see RequestInterface::setAction()
     *
     * @param   string $action
     * @return  $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }


    /**
     * @see RequestInterface::setActionParameter()
     *
     * @param   string $param
     * @return  $this
     */
    public function setActionParameter($param)
    {
        $this->actionParameter = $param;

        return $this;
    }


    /**
     * @see RequestInterface::setModule()
     *
     * @param   string $module
     * @return  $this
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }


    /**
     * @see RequestInterface::setModuleParameter()
     *
     * @param   string $param
     * @return  $this
     */
    public function setModuleParameter($param)
    {
        $this->moduleParameter = $param;

        return $this;
    }
}
