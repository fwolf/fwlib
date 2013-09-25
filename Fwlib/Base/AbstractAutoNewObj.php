<?php
namespace Fwlib\Base;


/**
 * Auto new property obj using magic function __get
 *
 * Should change to use trait after upgrade to PHP 5.4.
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-22
 */
abstract class AbstractAutoNewObj
{
    /**
     * Auto new property obj if not set and corresponding newObjFoo() exists
     *
     * @param   string  $name
     * @return  object
     */
    public function __get($name)
    {
        $method = 'newObj' . ucfirst($name);

        if (method_exists($this, $method)) {
            // NewObjFoo method exists, call it
            $this->$name = $this->$method();
            return $this->$name;
        } else {
            // @codeCoverageIgnoreStart

            $trace = debug_backtrace();
            trigger_error(
                'Undefined property via __get(): ' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE
            );

            // trigger_error will terminate program run, below will not exec
            return null;

            // @codeCoverageIgnoreEnd
        }
    }
}
