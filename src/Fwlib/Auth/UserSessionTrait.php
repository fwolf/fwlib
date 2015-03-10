<?php
namespace Fwlib\Auth;

use Fwlib\Util\UtilContainer;

/**
 * @see UserSessionInterface
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait UserSessionTrait
{
    /**
     * @var boolean
     */
    protected $isLoggedIn = false;


    /**
     * @see UserSessionInterface::clear()
     *
     * @return  static
     */
    public function clear()
    {
        $_SESSION = [];

        $this->isLoggedIn = false;

        return $this;
    }


    /**
     * Initialize, can be used in constructor
     *
     * @return  static
     */
    protected function initialize()
    {
        UtilContainer::getInstance()->getHttp()
            ->startSession();

        $this->load();
    }


    /**
     * @see UserSessionInterface::isLoggedIn()
     *
     * @return  boolean
     */
    public function isLoggedIn()
    {
        return $this->isLoggedIn;
    }


    /**
     * @see UserSessionInterface::load()
     *
     * @return  static
     */
    abstract public function load();
}
