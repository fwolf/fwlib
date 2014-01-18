<?php
namespace Fwlib\Auth;

use Fwlib\Auth\AuthenticationInterface;
use Fwlib\Auth\UserSessionInterface;

/**
 * Do authenticate and save user session if successful
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Cbtms@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-18
 */
abstract class AbstractAuthentication implements AuthenticationInterface
{
    /**
     * Authenticated user identity
     *
     * @var string
     */
    protected $identity = '';

    /**
     * Leave this null to do authenticate without save operate.
     *
     * @var UserSessionInterface
     */
    protected $userSession = null;


    /**
     * {@inheritdoc}
     */
    public function __construct(UserSessionInterface $userSession = null)
    {
        if (!is_null($userSession)) {
            $this->userSession = $userSession;
        }
    }


    /**
     * {@inheritdoc}
     */
    abstract public function authenticate();
}
