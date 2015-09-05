<?php
namespace Fwlib\Web;

use Fwlib\Base\SingletonTrait;
use Fwlib\Util\UtilContainer;

/**
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
     * @see RequestInterface::getAction()
     *
     * @return  string
     */
    public function getAction()
    {
        return UtilContainer::getInstance()->getHttp()
            ->getGet($this->actionParameter);
    }


    /**
     * @see RequestInterface::getModule()
     *
     * @return  string
     */
    public function getModule()
    {
        return UtilContainer::getInstance()->getHttp()
            ->getGet($this->moduleParameter);
    }
}
