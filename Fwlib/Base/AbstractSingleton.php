<?php
namespace Fwlib\Base;


/**
 * Singleton class template
 *
 * Should change to trait after PHP 5.4.0.
 *
 * @link http://www.phptherightway.com/pages/Design-Patterns.html
 *
 * @codeCoverageIgnore
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-07
 */
abstract class AbstractSingleton
{
    /**
     * Prevent clone method
     */
    private function __clone()
    {
    }


    /**
     * Prevent 'new' operator
     */
    protected function __construct()
    {
    }


    /**
     * Prevent unserialize method
     *
     * Removed because this prevent mock for test.
     */
    /*
    private function __wakeup()
    {
    }
    */


    /**
     * Get instance of Singleton itself
     *
     * @return  object
     */
    public static function getInstance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new static();
        }

        return $instance;
    }
}
