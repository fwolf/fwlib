<?php
namespace Fwlib\Bridge;

/**
 * Bridged Smarty class
 *
 * Added some helper method.
 *
 * For Smarty v3.x, which has not define namespace as PSR-0 require, so for
 * autoload original Smarty class, need add prefix to ClassLoader in
 * config.default.php footer part, or require Smarty.class.php somewhere.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Smarty extends \Smarty
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->left_delimiter = '{';
        $this->right_delimiter = '}';

        // Use subdir for speed when there are many tpl files
        $this->use_sub_dirs = true;
    }


    /**
     * Prepend to config_dir array
     *
     * @param   string|array    $configDir
     * @param   string          $key
     * @return  $this
     */
    public function addConfigDirPrepend($configDir, $key = '')
    {
        $dir = $this->getConfigDir();

        if (is_string($configDir)) {
            $configDir = [$key => $configDir];
        }

        $dir = array_merge($configDir, $dir);
        $this->setConfigDir($dir);

        return $this;
    }


    /**
     * Prepend to plugins_dir array
     *
     * @param   string|array    $pluginDir
     * @param   string          $key
     * @return  $this
     */
    public function addPluginsDirPrepend($pluginDir, $key = '')
    {
        $dir = $this->getPluginsDir();

        if (is_string($pluginDir)) {
            $pluginDir = [$key => $pluginDir];
        }

        $dir = array_merge($pluginDir, $dir);
        $this->setPluginsDir($dir);

        return $this;
    }


    /**
     * Prepend to template_dir array
     *
     * @param   string|array    $templateDir
     * @param   string          $key
     * @return  $this
     */
    public function addTemplateDirPrepend($templateDir, $key = '')
    {
        $dir = $this->getTemplateDir();

        if (is_string($templateDir)) {
            $templateDir = [$key => $templateDir];
        }

        $dir = array_merge($templateDir, $dir);
        $this->setTemplateDir($dir);

        return $this;
    }
}
