<?php
namespace Fwlib\Util;

use Fwlib\Util\AbstractUtilAware;

/**
 * FileSystem util
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2006-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2006-10-07
 */
class FileSystem extends AbstractUtilAware
{
    /**
     * Delete a dir or file recursive
     *
     * When del a dir, del all dir and files under it also.
     *
     * @param   string  $name
     */
    public function del($name)
    {
        // Lost link file will got nothing using realpath, basename, dirname
        // So trans in full path as $name all the time.
        if (!is_link($name)) {
            $name = realpath($name);
        }

        if (is_dir($name) && !is_link($name)) {
            foreach (scandir($name) as $file) {
                if (('.' == $file) || ('..' == $file)) {
                    continue;
                }
                $this->del($name . DIRECTORY_SEPARATOR . $file);
            }
            rmdir($name);
        } else {
            unlink($name);
        }
    }


    /**
     * Get dir name WITH ending slash
     *
     * In PHP, 'd/' means a dir under upper dir 'd', but this method will
     * return 'd/' instead.
     *
     * @param   string  $path
     * @return  string
     */
    public function getDirName($path)
    {
        if (empty($path)) {
            return '.' . DIRECTORY_SEPARATOR;
        }

        $i = strrpos($path, DIRECTORY_SEPARATOR);
        if (false === $i) {
            return '.' . DIRECTORY_SEPARATOR;
        } else {
            return pathinfo($path, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        }
    }


    /**
     * Count size of a directory recursive
     *
     * @param   string  $path
     * @param   boolean $blksize
     * @return  long
     */
    public function getDirSize($path, $blksize = false)
    {
        if (is_file($path)) {
            return $this->getFileSize($path, $blksize);
        }

        // Dir
        if (DIRECTORY_SEPARATOR != substr($path, -1)) {
            $path .= DIRECTORY_SEPARATOR;
        }

        $i = 0;
        $files = scandir($path);
        foreach ($files as $file) {
            if (('.' != $file) && ('..' != $file)) {
                $fullpath = $path . $file;
                if (is_dir($fullpath)) {
                    $i += $this->getDirSize($fullpath, $blksize);
                } else {
                    $i += $this->getFileSize($fullpath, $blksize);
                }
            }
        }
        return $i;
    }


    /**
     * Get extension of file
     *
     * @param   string  $filename
     * @return  string
     */
    public function getFileExt($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }


    /**
     * Get filename without ext
     *
     * @param   string  $filename
     * @return  string
     */
    public function getFileName($filename)
    {
        return pathinfo($filename, PATHINFO_FILENAME);
    }


    /**
     * Get a filename to write as new, skip exists file
     *
     * If file with same name exists, will add -1, -2, -nnn at end of filename
     * before extention, until get a filename not exists.
     *
     * Will also remove special chars in filename.
     *
     * Can use with dir as well as regular file.
     *
     * @param   string  $file   Path to dest file
     * @return  string
     */
    public function getFileNameForNew($file)
    {
        $file = trim($file);

        // Remove special chars in filename
        $file = str_replace(
            array('?', '&', ';', '=', ':', "\\"),
            '-',
            $file
        );

        $dir  = $this->getDirName($file);
        $name = $this->getFileName($file);
        $ext  = $this->getFileExt($file);

        // Auto skip exists file, no overwrite.(-1, -2...-9, -10, -11.ext)
        $i = 1;
        while (file_exists($file)) {
            $file = $dir . $name . '-' . strval($i ++) .
                (empty($ext) ? '' : ('.' . $ext));
        }

        return $file;
    }


    /**
     * Count size of a file
     *
     * If $blksize = true, return actual block size file occupy.
     *
     * 11 = blksize, blocksize of filesystem IO
     * 12 = blocks, number of 512 bytes block allocated
     *
     * @link    http://linux.die.net/man/2/stat
     * @param   string  $file
     * @param   boolean $blksize    Get blksize instead of native filesize
     * @return  long
     */
    public function getFileSize($file, $blksize = false)
    {
        if (is_link($file)) {
            $stat = lstat($file);
        } else {
            $stat = stat($file);
        }

        if (!$blksize || -1 == $stat['blksize']) {
            return $stat['size'];
        } else {
            return ceil($stat['blocks'] * 512 / $stat['blksize'])
                * $stat['blksize'];
        }
    }


    /**
     * List file with information of a directory
     *
     * @param   string  $dir
     * @param   string  $sortby     Sort list by: name, mtime, size
     * @param   string  $order      Sort order: ASC, DESC
     * @return  array
     */
    public function listDir($dir = './', $sortby = '', $order = 'ASC')
    {
        // List files
        $dir = realpath($dir);
        if (empty($dir) || !is_dir($dir)) {
            return(null);
        }
        $dirfiles = scandir($dir);
        // @codeCoverageIgnoreStart
        if (empty($dirfiles)) {
            return(array());
        }
        // @codeCoverageIgnoreEnd
        $dir .= DIRECTORY_SEPARATOR;


        // Get file information, ignore '.', '..'
        $arFiles = array();
        foreach ($dirfiles as $file) {
            if (('.' != $file) && ('..' != $file)) {
                $fullpath = $dir . $file;

                if (is_dir($fullpath)) {
                    $size = $this->getDirSize($fullpath);
                } else {
                    $size = $this->getFileSize($fullpath);
                }

                $arFiles[] = array(
                    'name'  => $file,
                    'mtime' => filemtime($dir . $file),
                    'size'  => $size,
                );
            }
        }


        // Sort result
        if (!empty($sortby)) {
            $arrayUtil = $this->utilContainer->get('Array');
            $arrayUtil->sortByLevel2($arFiles, $sortby, $order);
        }

        return $arFiles;
    }
}
