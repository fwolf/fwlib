<?php
namespace Fwlib\Html\Generator;

use Fwlib\Html\Generator\Exception\ElementNotFoundException;

/**
 * Produce element instance
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ElementFactory
{
    /**
     * Prefixes of element classes
     *
     * Element classes can be stored in several directories. When create
     * element instance, only last segment or segments of element class name
     * are needed.  Factory will try to find full element class name by add
     * these prefixes one-by-one.
     *
     * Prefix MUST end with '\'.
     *
     * User should better overwrite {@see getElementClassPrefixes()} for
     * customize prefixes.
     *
     * @var string[]
     */
    protected $elementClassPrefixes = [
        'Fwlib\\Html\\Generator\\Element\\',
    ];


    /**
     * Create element instance
     *
     * Use of full qualified class name are supported, MUST start with '\'.
     *
     * @param   string $className
     * @return  ElementInterface
     */
    public function create($className)
    {
        if ('\\' == substr($className, 0, 1)) {
            $fullClassName = substr($className, 1);
        } else {
            $fullClassName = $this->getFullClassName($className);
        }

        $instance = new $fullClassName;

        return $instance;
    }


    /**
     * @return  \string[]
     */
    protected function getElementClassPrefixes()
    {
        return $this->elementClassPrefixes;
    }


    /**
     * Get full class name of element
     *
     * @param   string $className
     * @return  string
     * @throws  ElementNotFoundException
     */
    protected function getFullClassName($className)
    {
        $prefixes = $this->getElementClassPrefixes();

        foreach ($prefixes as $prefix) {
            $fullClassName = $prefix . ucfirst($className);
            if (class_exists($fullClassName)) {
                return $fullClassName;
            }
        }

        throw new ElementNotFoundException(
            "Html Element '$className' not found"
        );
    }
}
