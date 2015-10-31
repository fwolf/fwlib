<?php
namespace Fwlib\Base;

/**
 * ClassLoader implement PSR-4
 *
 * Notice: Composer and its autoloader will be enough good in most case.
 * Although this class can still used to load old library not support composer.
 *
 * Usage:
 *
 *  require 'path/to/ClassLoader.php';
 *
 *  use Fwlib\Base\ClassLoader;
 *
 *  $classLoader = ClassLoader::getInstance();
 *
 *  // Root namespace
 *  $classLoader->addPrefix('Fwlib', 'path/to/dir/contain/fwlib/Fwlib/');
 *
 *  // Sub namespace define can be after parent namespace
 *  $classLoader->addPrefix('Fwlib\\Base',
 *  'path/to/another/dir/contain/fwlib/Fwlib/Base/');
 *
 *  // Standalone class(not implement PSR-4) use full path
 *  $classLoader->addPrefix('FooClass', 'path/to/FooClass.php');
 *
 *  // Search include_path at last
 *  $classLoader->useIncludePath = true;
 *
 *  // Register autoloader
 *  $classLoader->register();
 *
 * Path can be array, ClassLoader will try each path in it.
 *
 * See autoload.php in Fwlib root path for example.
 *
 * Ref:
 * - https://wiki.php.net/rfc/splclassloader
 * - https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4.md
 * -
 * https://github.com/symfony/symfony/blob/master/src/Symfony/Component/ClassLoader/ClassLoader.php
 *
 * This class can use Singleton pattern, but its used before autoloader is
 * registered , require AbstractSingleton class need hardcoded relative path.
 * To keep things simple, copy need method here.
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ClassLoader
{
    /**
     * Extension of class file to load
     *
     * @var string
     */
    public $fileExtension = '.php';

    /**
     * Namespace - path array
     *
     * [{prefix: path}]
     *
     * Prefix have no leading or tailing \.
     *
     * @var array
     */
    public $prefix = [];

    /**
     * Namespace separator, default '\'
     *
     * @var string
     */
    public $prefixSeparator = '\\';

    /**
     * Look for file in include_path as last try
     *
     * @var boolean
     */
    public $useIncludePath = false;


    /**
     * Constructor
     *
     * @param   string|array $prefix Prefix or array of prefix-path
     * @param   string|array $path
     */
    public function __construct($prefix = null, $path = null)
    {
        if (!empty($prefix) && !empty($path)) {
            $this->addPrefix($prefix, $path);
        }
    }


    /**
     * Add new prefix
     *
     * @param   string|array $prefix Prefix or array of prefix-path
     * @param   string|array $path
     * @return  static
     */
    public function addPrefix($prefix, $path = null)
    {
        if (is_array($prefix)) {
            // $prefix is array of prefix-path
            $this->prefix = array_merge($this->prefix, $prefix);

        } else {
            $this->prefix[$prefix] = $path;
        }

        return $this;
    }


    /**
     * Find file in prefix, include_path
     *
     * @param   string $prefix
     * @param   string $fileName Without namespace
     * @return  string|bool             Valid file path or false
     */
    public function findFile($prefix, $fileName)
    {
        // Each prefix may have multiple path, so dest file is array
        $arFile = [];


        // Match possible file path
        if (empty($fileName)) {
            // Standalone class
            if (isset($this->prefix[$prefix])) {
                $arFile = (array)$this->prefix[$prefix];
            } else {
                return false;
            }

        } else {
            // Replace \ in prefix to /
            // Replace _ in ClassName to /
            // Add file extension
            $filePath = str_replace(
                    $this->prefixSeparator,
                    DIRECTORY_SEPARATOR,
                    $prefix
                ) . DIRECTORY_SEPARATOR . str_replace(
                    '_',
                    DIRECTORY_SEPARATOR,
                    $fileName
                ) . $this->fileExtension;


            // Match prefix by layer
            $pos = strrpos($prefix, $this->prefixSeparator);
            while (0 < strlen($prefix)) {
                if (isset($this->prefix[$prefix])) {
                    foreach ((array)$this->prefix[$prefix] as $path) {
                        // Add tailing / to path
                        if (DIRECTORY_SEPARATOR != substr($path, -1)) {
                            $path .= DIRECTORY_SEPARATOR;
                        }

                        // Replace leading matched part in path with prefix path
                        $prefixPath = str_replace(
                                $this->prefixSeparator,
                                DIRECTORY_SEPARATOR,
                                $prefix
                            ) . DIRECTORY_SEPARATOR;
                        $prefixPath = preg_quote($prefixPath, '/');
                        $arFile[] = preg_replace(
                            "/^$prefixPath/",
                            $path,
                            $filePath
                        );
                    }

                    break;
                }

                // Goto upper layer namespace
                $prefix = substr($prefix, 0, intval($pos));
                $pos = strrpos($prefix, $this->prefixSeparator);
            }


            // FilePath start from current path ?
            $arFile[] = $filePath;
        }


        return $this->validateFiles($arFile);
    }


    /**
     * Get instance of Singleton itself
     *
     * @return  static
     */
    public static function getInstance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new static();
        }

        return $instance;
    }


    /**
     * Load given class or interface
     *
     * Will auto trigger after register to spl_autoload.
     *
     * @param   string $className With full qualified namespace
     * @return  boolean                 Return false when load file not exists
     */
    public function loadClass($className)
    {
        // Both \ and _ are supported, will convert to inner separator
        $className = str_replace(
            ['\\', '_'],
            $this->prefixSeparator,
            $className
        );


        $pos = strrpos($className, $this->prefixSeparator);
        if (false !== $pos) {
            // With namespace
            $prefix = substr($className, 0, $pos);
            $fileName = substr($className, $pos + 1);
        } else {
            // Without namespace
            $prefix = $className;
            $fileName = '';
        }


        $file = $this->findFile($prefix, $fileName);
        if (false === $file) {
            return false;
        } else {
            /** @noinspection PhpIncludeInspection */
            return require($file);
        }
    }


    /**
     * Register using spl_autoload_register
     *
     * @param   boolean $prepend
     * @return  static
     */
    public function register($prepend = false)
    {
        spl_autoload_register([$this, 'loadClass'], true, $prepend);

        return $this;
    }


    /**
     * Unregister using spl_autoload_unregister
     *
     * @return  static
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'loadClass']);

        return $this;
    }


    /**
     * Check file existence and return first valid
     *
     * Will also try include_path option.
     *
     * @param   string[] $files
     * @return  string|bool
     */
    protected function validateFiles(array $files)
    {
        foreach ($files as $file) {
            if (file_exists($file) || ($this->useIncludePath &&
                    file_exists(stream_resolve_include_path($file)))
            ) {
                return $file;
            }
        }

        return false;
    }
}
