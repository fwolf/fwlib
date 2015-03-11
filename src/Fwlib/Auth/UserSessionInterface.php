<?php
namespace Fwlib\Auth;

/**
 * User session accessor
 *
 * Provide user and session info to ACL, or directly use as a simple ACL.
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface UserSessionInterface
{
    /**
     * Clear session data, include storage
     *
     * @return  static
     */
    public function clear();


    /**
     * Currently is logged in
     *
     * @return  boolean
     */
    public function isLoggedIn();


    /**
     * Load session from storage
     *
     * @return  static
     */
    public function load();


    /**
     * Save session to storage
     *
     * Should throw exception if currently is not logged in.
     *
     * @return  static
     */
    public function save();


    /**
     * Setter of $sessionHandler
     *
     * @param   SessionHandlerInterface $sessionHandler
     * @return  static
     */
    public function setSessionHandler(SessionHandlerInterface $sessionHandler);
}
