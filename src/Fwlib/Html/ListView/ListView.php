<?php
namespace Fwlib\Html\ListView;

use Fwlib\Config\ConfigAwareTrait;

/**
 * ListView
 *
 * Migrate from old ListTable.
 *
 *
 * Config class and id will be used in html, css and js.
 *
 * Class is classname of root element, and classname prefix of other child
 * elements. should not be empty.
 *
 * Id is identify of a list, the actual html id will prefix with class, should
 * not be empty.
 *
 * Example:
 *  <div class='listView' id='listView-1'>
 *    <div class='listView__pager' id='listView-1__pager'>
 *
 *
 * @copyright   Copyright 2003-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ListView
{
    use ConfigAwareTrait;


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
     * @return array
     */
    protected function getDefaultConfigs()
    {
        return [
            'class'             => 'listView',
            'id'                => 1,
        ];
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
