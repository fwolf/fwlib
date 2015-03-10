<?php
namespace Fwlib\Auth\SessionHandler\Exception;

/**
 * Extension for set session id when its opened
 *
 * If allowed, the id newly set has no affect, and will be use wrongly when
 * call id getter. For new session id, destroy and reopen or regenerate new one.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ChangeOpenedSessionIdException extends \Exception
{
}
