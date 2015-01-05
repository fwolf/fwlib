<?php
namespace Fwlib\Auth;

/**
 * Do authenticate and save user session if successful
 *
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
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


    /**
     * {@inheritdoc}
     */
    public function getIdentity()
    {
        return $this->identity;
    }
}
