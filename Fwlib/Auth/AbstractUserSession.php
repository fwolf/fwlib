<?php
namespace Fwlib\Auth;

use Fwlib\Auth\UserSessionInterface;

/**
 * User session accessor
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Cbtms@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-18
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
        session_start();

        $this->load();
    }


    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        session_unset();

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
