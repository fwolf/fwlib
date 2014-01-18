<?php
namespace Fwlib\Auth;


/**
 * User session accessor
 *
 * Provide user and session info to ACL, or directly use as a simple ACL.
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Cbtms@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-18
 */
interface UserSessionInterface
{
    /**
     * Constructor
     *
     * Start session, try to load session from storage.
     */
    public function __construct();


    /**
     * Clear session data, include storage
     *
     * @return  UserSessionInterface
     */
    public function clear();


    /**
     * Currently is logined
     *
     * @return  boolean
     */
    public function isLogined();


    /**
     * Load session from storage
     *
     * @return  UserSessionInterface
     */
    public function load();


    /**
     * Save session to storage
     *
     * Should throw exception if currently is not logined.
     *
     * @return  UserSessionInterface
     */
    public function save();
}
