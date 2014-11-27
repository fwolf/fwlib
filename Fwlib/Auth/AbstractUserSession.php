<?php
namespace Fwlib\Auth;

use Fwlib\Util\UtilContainer;

/**
 * User session accessor
 *
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
abstract class AbstractUserSession implements UserSessionInterface
{
    /**
     * @var boolean
     */
    protected $isLogined = false;


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

        $this->isLogined = false;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function isLogined()
    {
        return $this->isLogined;
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
