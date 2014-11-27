<?php
namespace Fwlib\Auth;

/**
 * Do authenticate and save user session if successful
 *
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
interface AuthenticationInterface
{
    /**
     * Constructor
     *
     * @param   UserSessionInterface    $userSession
     */
    public function __construct(UserSessionInterface $userSession = null);


    /**
     * Do authenticate, got identity and save session
     *
     * @return  boolean
     */
    public function authenticate();


    /**
     * Get authenticated identity
     *
     * @return  string
     */
    public function getIdentity();
}
