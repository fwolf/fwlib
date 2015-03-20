<?php
namespace Fwlib\Web\Helper;

use Fwlib\Util\UtilContainer;
use Fwlib\Web\Exception\ViewMethodNotDefinedException;
use Fwlib\Web\RequestInterface;

/**
 * Trait for view whose body is generated by fetch methods
 *
 * Output body is retrieved from corresponding method, whose name is converted
 * from request action by adding prefix. Eg, action 'foo-bar' will call
 * fetchFooBar() for result. Prefix is defined in {@see $methodPrefix}.
 *
 * Need 'body' in output parts.
 *
 * @method  RequestInterface    getRequest()
 *
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait GetOutputBodyByFetchMethodTrait
{
    /**
     * Prefix of method to generate output
     *
     * @var string
     * @see getOutputBody()
     */
    protected $methodPrefix = 'fetch';


    /**
     * Get output of body part
     *
     * @return  string
     * @throws  ViewMethodNotDefinedException
     */
    protected function getOutputBody()
    {
        $action = $this->getRequest()->getAction();
        if (empty($action)) {
            return '';
        }

        $stringUtil = UtilContainer::getInstance()->getString();

        $method = $this->methodPrefix . $stringUtil->toStudlyCaps($action);
        if (!method_exists($this, $method)) {
            throw new ViewMethodNotDefinedException(
                "View {$this->methodPrefix} method for " .
                "action {$action} is not defined"
            );
        }

        return $this->$method();
    }
}
