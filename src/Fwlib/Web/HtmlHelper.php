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
     * Css references
     *
     * Format: {name: {href, media}}
     *
     * For media type and query:
     * @see https://developer.mozilla.org/en-US/docs/Web/CSS/@media
     * @see https://developer.mozilla.org/en-US/docs/Web/Guide/CSS/Media_queries
     *
     * @var array
     */
    protected $css = [];

    /**
     * Javascript references
     *
     * @var string[]
     */
    protected $javascript = [];

    /**
     * @var string
     */
    protected $rootPath = '';


    /**
     * Add a css reference
     *
     * @param   string  $name
     * @param   string  $href
     * @param   string  $media
     * @return  static
     */
    public function addCss($name, $href, $media = 'all')
    {
        $this->css[$name] = [
            'href'  => $href,
            'media' => $media,
        ];

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
        $this->javascript[$name] = $path;

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
        $this->javascript = [];

        return $this;
    }


    /**
     * Getter of css reference
     *
     * @param   string  $name   Use '*' for all, or string name for single.
     * @return  array
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
            return $this->javascript;

        } elseif (array_key_exists($name, $this->javascript)) {
            return $this->javascript[$name];

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
        unset($this->javascript[$name]);

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
