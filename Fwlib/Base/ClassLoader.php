<?php
namespace Fwlib\Base;


/**
 * ClassLoader implement PSR-0
 *
 * Usage:
 *
 *  require 'path/to/ClassLoader.php';
 *
 *  use Fwlib\Base\ClassLoader;
 *
 *  // Subnamespace define should before parent namespace
 *  ClassLoader::addPrefix('Fwlib\\Base', 'path/to/another/Fwlib/contain/dir/');
 *
 *  // Root namespace
 *  ClassLoader::addPrefix('Fwlib', 'path/to/Fwlib/contain/dir/');
 *
 *  // Standalone class(not implement PSR-0) use full path
 *  ClassLoader::addPrefix('FooClass', 'path/to/FooClass.php');
 *
 *  // Search include_path at last
 *  ClassLoader::$useIncludePath = true;
 *
 *  // Register autoloader
 *  ClassLoader::register();
 *
 * Path can be array, ClassLoader will try each path in it.
 *
 * See autoload.php in Fwlib root path for example.
 *
 * Ref:
 * - https://wiki.php.net/rfc/splclassloader
 * - https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 * - https://github.com/symfony/symfony/blob/master/src/Symfony/Component/ClassLoader/ClassLoader.php
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-07-27
 */
class ClassLoader
{
    /**
     * Extension of class file to load
     * @var string
     */
    public static $fileExtension = '.php';

    /**
     * Namespace - path array
     *
     * [{prefix: path}]
     *
     * Prefix have no leading or tailing \.
     *
     * @var array
     */
    public static $prefix = array();

    /**
     * Namespace separator, default '\'
     * @var string
     */
    public static $prefixSeparator = '\\';

    /**
     * Look for file in include_path as last try
     * @var boolean
     */
    public static $useIncludePath = false;


    /**
     * Constructor
     *
     * Useless for static class now.
     *
     * @param   mixed   $prefix         Prefix or array of prefix-path
     * @param   string  $path
     */
    public function __construct($prefix = null, $path = null)
    {
        if (!empty($prefix)) {
            $this->addPrefix($prefix, $path);
        }
    }


    /**
     * Add new prefix
     *
     * @param   mixed   $prefix         Prefix or array of prefix-path
     * @param   string  $path
     * @return  self
     */
    public static function addPrefix($prefix, $path = null)
    {
        if (is_array($prefix)) {
            // Array of prefix-path
            self::$prefix = array_merge(self::$prefix, $prefix);
        } else {
            self::$prefix[$prefix] = $path;
        }

        return __CLASS__;
    }


    /**
     * Find file in prefix, include_path
     *
     * @param   string  $prefix
     * @param   string  $fileName       Without namespace
     * @return  mixed                   Exists file path or false
     */
    public static function findFile($prefix, $fileName)
    {
        // Each prefix may have multiple path, so dest file is array
        $arFile = array();


        // Match possible filepath
        if (empty($fileName)) {
            // Standalone class
            if (isset(self::$prefix[$prefix])) {
                $arFile = (array)self::$prefix[$prefix];
            } else {
                return false;
            }
        } else {
            // Replace \ in perfix to /
            // Replace _ in ClassName to /
            // Add file extension
            $filePath = str_replace(
                self::$prefixSeparator,
                DIRECTORY_SEPARATOR,
                $prefix
            ) . DIRECTORY_SEPARATOR . str_replace(
                '_',
                DIRECTORY_SEPARATOR,
                $fileName
            ) . self::$fileExtension;


            // Match prefix by layer
            $found = false;
            $pos = strrpos($prefix, self::$prefixSeparator);
            while (!$found && (0 < strlen($prefix))) {
                if (isset(self::$prefix[$prefix])) {
                    $found = true;

                    foreach ((array)self::$prefix[$prefix] as $path) {
                        // Add tailing / to path
                        if (DIRECTORY_SEPARATOR != substr($path, -1)) {
                            $path .= DIRECTORY_SEPARATOR;
                        }

                        $arFile[] = $path . $filePath;
                    }

                    break;
                }

                // Goto upper layer namespace
                $prefix = substr($prefix, 0, intval($pos));
                $pos = strrpos($prefix, self::$prefixSeparator);
            }
        }


        // No match
        if (empty($arFile)) {
            return false;
        }


        // Check file existence and try include_path
        foreach ($arFile as $file) {
            if (file_exists($file) || (self::$useIncludePath
                && file_exists(stream_resolve_include_path($file)))
            ) {
                return $file;
            }
        }

        // All match file not exists
        return false;
    }


    /**
     * Load given class or interface
     *
     * Will auto trigger after register to spl_autoload
     *
     * @param   string  $className      With qualified namespace
     * @return  boolean                 Return false when load file not exists
     */
    public static function loadClass($className)
    {
        $pos = strrpos($className, self::$prefixSeparator);
        if (false !== $pos) {
            // Start with namespace\
            $prefix = substr($className, 0, $pos);
            $fileName = substr($className, $pos + 1);
        } else {
            // No namespace
            $prefix = $className;
            $fileName = '';
        }


        $file = self::findFile($prefix, $fileName);
        if (false === $file) {
            return false;
        } else {
            return require($file);
        }
    }


    /**
     * Register using spl_autoload_register
     *
     * @param   boolean $prepend
     * @return  self
     */
    public static function register($prepend = false)
    {
        spl_autoload_register(array(__CLASS__, 'loadClass'), true, $prepend);
        return __CLASS__;
    }


    /**
     * Unregister using spl_autoload_unregister
     *
     * @return  self
     */
    public static function unregister()
    {
        spl_autoload_unregister(array(__CLASS__, 'loadClass'));
        return __CLASS__;
    }
}
