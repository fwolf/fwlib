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
     * @return  UserSessionInterface
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
     * @return  UserSessionInterface
     */
    public function load();


    /**
     * Save session to storage
     *
     * Should throw exception if currently is not logged in.
     *
     * @return  UserSessionInterface
     */
    public function save();
}
