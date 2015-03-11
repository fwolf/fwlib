<?php
namespace Fwlib\Auth;

/**
 * @see UserSessionInterface
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait UserSessionTrait
{
    use SessionHandlerAwareTrait;


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
        $this->getSessionHandler()->clear();

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
        $this->getSessionHandler()->open();

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
