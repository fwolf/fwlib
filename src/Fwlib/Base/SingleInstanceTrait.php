<?php
namespace Fwlib\Base;

/**
 * Trait of getInstance() aware
 *
 * Singleton or container classes commonly have only one instance, and a
 * static getInstance() method are used to create and return its instance.
 *
 * Only for reuse, should not use as type hint, so no relevant interface,.
 *
 * The difference between this and singleton is, this class does not strictly
 * prohibit multiple instances, maybe useful in special cases.
 *
 * Can not use when constructor need parameters.
 *
 *
 * Notice: If a class Foo use this trait, then extend by another child class
 * Bar, the Bar class may need use this trait too, because 'static' is bind to
 * Foo, the class which trait is used in. To work properly, Bar need its own
 * static method -- use this trait too.
 *
 * Copy getInstance() to class use this trait may work, only when parent class
 * Foo is not used. If Foo::getInstance() is called before Bar::getInstance(),
 * the static instance is generated in Foo, and skipped in Bar, we will got a
 * wrong instance type. This also happen when Bar called first.
 *
 * Another solution is make static instance as array, stores every instance
 * it is called in, with get_called_class(), here use this one.
 *
 * Make static instance property of class instead of method may also works,
 * but this property may cause name conflict with other property in child class.
 * If use this play, choose a good name.
 *
 * @see https://bugs.php.net/bug.php?id=65039
 * @see http://php.net/manual/en/language.oop5.late-static-bindings.php
 * @see \Fwlib\Base\ServiceContainer
 * @see \FwlibTest\Aide\TestServiceContainer
 *
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait SingleInstanceTrait
{
    /**
     * Get instance of self
     *
     * @return  static
     */
    public static function getInstance()
    {
        static $instance = [];

        $className = get_called_class();

        if (!isset($instance[$className])) {
            $instance[$className] = new $className();
        }

        return $instance[$className];
    }
}
