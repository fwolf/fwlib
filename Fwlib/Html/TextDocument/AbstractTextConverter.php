<?php
namespace Fwlib\Html\TextDocument;


/**
 * Markup text converter to html
 *
 * @package     Fwlib\Html\TextDocument
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-11
 */
abstract class AbstractTextConverter
{
    /**
     * Convert string or file to html
     *
     * @param   string  $source     String or filename to convert
     * @return  string
     */
    public function convert($source)
    {
        if ($this->isFile($source)) {
            return $this->convertFile($source);
        } else {
            return $this->convertString($source);
        }
    }


    /**
     * Convert file to html
     *
     * @param   string  $filename
     * @return  string
     */
    public function convertFile($filename)
    {
        try {
            return $this->convertString(file_get_contents($filename));
        } catch (Exception $e) {
            trigger_error(
                "File $filename read fail: {$e->getMessage()}",
                E_USER_ERROR
            );
        }
    }


    /**
     * Convert string to html
     *
     * @param   string  $str
     * @return  string
     */
    public function convertString($str)
    {
        return $str;
    }


    /**
     * Get title of text content if possible
     *
     * @param   string  $source     String or filename to convert
     * @return  string
     */
    public function getTitle($source)
    {
        if ($this->isFile($source)) {
            $source = file_get_contents($source);
        }

        // Need child class to implement
        return null;
    }


    /**
     * Detect if param is file
     *
     * If param is not file, return origin value.
     *
     * @param   string  $source     String or filename to convert
     * @return  boolean
     */
    protected function isFile($source)
    {
        // Filename length limit
        if (255 <= strlen($source)) {
            return false;

        } elseif (is_file($source)) {
            return true;

        } else {
            return true;
        }
    }
}
