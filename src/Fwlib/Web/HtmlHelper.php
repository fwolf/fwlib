<?php
namespace Fwlib\Web;

use Fwlib\Base\SingleInstanceTrait;

/**
 * Helper class for generate html output
 *
 *
 * Css and js reference(path) are stored here, for various class to access
 * shared css and js collection, then output them in result html. This class
 * only provide accessors, no html generation here.
 *
 * Root path should set at application beginning, is a path to public root,
 * used for generate path of other resources, these path usually used in url.
 *
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HtmlHelper
{
    use SingleInstanceTrait;


    /**
     * @var string[]
     */
    protected $css = [];

    /**
     * @var string[]
     */
    protected $js = [];

    /**
     * @var string
     */
    protected $rootPath = '';


    /**
     * Add a css reference
     *
     * @param   string  $name
     * @param   string  $path
     * @return  static
     */
    public function addCss($name, $path)
    {
        $this->css[$name] = $path;

        return $this;
    }


    /**
     * Add a js reference
     *
     * @param   string  $name
     * @param   string  $path
     * @return  static
     */
    public function addJs($name, $path)
    {
        $this->js[$name] = $path;

        return $this;
    }


    /**
     * Clear all css reference
     *
     * @return  static
     */
    public function clearCss()
    {
        $this->css = [];

        return $this;
    }


    /**
     * Clear all js reference
     *
     * @return  static
     */
    public function clearJs()
    {
        $this->js = [];

        return $this;
    }


    /**
     * Getter of css reference
     *
     * @param   string  $name   Use '*' for all, or string name for single.
     * @return  array|string
     */
    public function getCss($name = '*')
    {
        if ('*' == $name) {
            return $this->css;

        } elseif (array_key_exists($name, $this->css)) {
            return $this->css[$name];

        } else {
            return null;
        }
    }


    /**
     * Getter of js reference
     *
     * @param   string  $name   Use '*' for all, or string name for single.
     * @return  array|string
     */
    public function getJs($name = '*')
    {
        if ('*' == $name) {
            return $this->js;

        } elseif (array_key_exists($name, $this->js)) {
            return $this->js[$name];

        } else {
            return null;
        }
    }


    /**
     * @return  string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }


    /**
     * Remove a css reference
     *
     * @param   string  $name
     * @return  static
     */
    public function removeCss($name)
    {
        unset($this->css[$name]);

        return $this;
    }


    /**
     * Remove a js reference
     *
     * @param   string  $name
     * @return  static
     */
    public function removeJs($name)
    {
        unset($this->js[$name]);

        return $this;
    }


    /**
     * @param   string  $rootPath
     * @return  static
     */
    public function setRootPath($rootPath)
    {
        if (!empty($rootPath) &&
            DIRECTORY_SEPARATOR != substr($rootPath, -1)
        ) {
            $rootPath .= DIRECTORY_SEPARATOR;
        }

        $this->rootPath = $rootPath;

        return $this;
    }
}
