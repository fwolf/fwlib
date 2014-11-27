<?php
namespace Fwlib\Auth;

use Fwlib\Util\UtilContainer;

/**
 * User session accessor
 *
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractUserSession implements UserSessionInterface
{
    /**
     * @var boolean
     */
    protected $isLoggedIn = false;


    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        UtilContainer::getInstance()->getHttp()
            ->startSession();

        $this->load();
    }


    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $_SESSION = array();

        $this->isLoggedIn = false;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function isLoggedIn()
    {
        return $this->isLoggedIn;
    }


    /**
     * {@inheritdoc}
     */
    abstract public function load();


    /**
     * {@inheritdoc}
     */
    abstract public function save();
}
