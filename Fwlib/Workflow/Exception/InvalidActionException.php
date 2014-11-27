<?php
namespace Fwlib\Workflow\Exception;

/**
 * Exception for execute action not available
 *
 * The reason maybe action not defined, or user use refresh to re-submit form.
 * For the 2nd reason, previous submit has executed the action, currentNode
 * are moved, so the action is not exists in currentNode.
 *
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class InvalidActionException extends \Exception
{
}
