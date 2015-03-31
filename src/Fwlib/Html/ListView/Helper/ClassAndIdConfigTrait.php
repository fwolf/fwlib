<?php
namespace Fwlib\Html\ListView\Helper;

/**
 * Trait of getter and setter of class and id config
 *
 * Class and id are stored in configs, both should not be empty. They will be
 * used in html, css and js, for root and all child elements. This trait can
 * supply same name combine mechanism through all list view components.
 *
 * Class and id can be set from outside throw {@see ListView}, and
 * {@see ListView} will set them to components.
 *
 *
 * Class is classname of root element, and classname prefix of other child
 * elements.
 *
 * Id is identify of a list, the actual root element id will prefix with
 * class. The prefixed root element id will be prefix of child element id.
 *
 * Example:
 *  <div class='listView' id='listView-1'>
 *    <div class='listView__pager' id='listView-1__pager'>
 *
 *
 * @method  string  getConfig($key)
 * @method  static  setConfig($key, $value)
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ClassAndIdConfigTrait
{
    /**
     * Get element class name
     *
     * @param   string  $name   Empty for root element
     * @return  string
     */
    protected function getClass($name = '')
    {
        $class = $this->getConfig('class');

        if (!empty($name)) {
            $class .= "__$name";
        }

        return $class;
    }


    /**
     * Get element id
     *
     * @param   string  $name   Empty for root element
     * @return  string
     */
    protected function getId($name = '')
    {
        $identity = $this->getConfig('id');
        $rootId = $this->getConfig('class') .
            (empty($identity) ? '' : "-$identity");

        return empty($name) ? $rootId
            : $rootId . "__$name";
    }


    /**
     * Setter of root class
     *
     * @param   string  $class
     * @return  static
     */
    public function setClass($class)
    {
        $this->setConfig('class', $class);

        return $this;
    }


    /**
     * Setter of $id
     *
     * @param   int|string  $identity
     * @return  static
     */
    public function setId($identity)
    {
        $this->setConfig('id', $identity);

        return $this;
    }
}
