<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\FileSystem;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2010-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2010-10-10
 */
class FileSystemTest extends PHPunitTestCase
{
    protected $fileSystem;
    protected $utilContainer;


    public function __construct()
    {
        $this->utilContainer = UtilContainer::getInstance();
        $this->fileSystem = new FileSystem;
        $this->fileSystem->setUtilContainer($this->utilContainer);
    }


    public function testDel()
    {
        // Dir
        $dir = sys_get_temp_dir() . '/test.Fwlib.Util.FileSystem/';
        if (file_exists($dir)) {
            $this->fileSystem->del($dir);
        }
        mkdir($dir);
        // File
        $foo1 = tempnam($dir, 'foo');
        // Dir under dir
        mkdir($dir . 'dir');
        // File under dir
        $bar1 = tempnam($dir . 'dir', 'bar1');
        $bar2 = tempnam($dir . 'dir', 'bar2');
        // Hard link
        link($foo1, $dir . 'dir/hardlink-to-foo1');
        link($bar1, $dir . 'hardlink-to-bar1');
        // Symlink
        symlink($foo1, $dir . 'dir/symlink-to-foo1');
        symlink($bar2, $dir . 'symlink-to-bar2');


        // Test dir/file size
        file_put_contents($foo1, 'blah');
        $this->assertEquals(4, $this->fileSystem->getFileSize($foo1));
        $this->assertEquals(
            4,
            $this->fileSystem->getFileSize($dir . 'dir/hardlink-to-foo1')
        );
        $this->assertTrue($this->fileSystem->getDirSize($foo1, true) > 4);
        $this->assertTrue(
            $this->fileSystem->getDirSize($dir) < $this->fileSystem->getDirSize($dir, true)
        );


        // Test del
        $this->fileSystem->del($dir);
        $this->assertFileNotExists($dir);
    }


    public function testGetDirName()
    {
        $x = 'a/';
        $this->assertEquals('./', $this->fileSystem->getDirName($x));

        $x = 'a/b/c/d';
        $this->assertEquals('a/b/c/', $this->fileSystem->getDirName($x));

        $x = null;
        $this->assertEquals('./', $this->fileSystem->getDirName($x));
        $x = '';
        $this->assertEquals('./', $this->fileSystem->getDirName($x));
        $x = '42';
        $this->assertEquals('./', $this->fileSystem->getDirName($x));
    }


    public function testGetFileExt()
    {
        $x = 'a.txt';
        $this->assertEquals('txt', $this->fileSystem->getFileExt($x));

        $x = 'a.b.c.d.txt';
        $this->assertEquals('txt', $this->fileSystem->getFileExt($x));

        $x = null;
        $this->assertEquals('', $this->fileSystem->getFileExt($x));
        $x = '';
        $this->assertEquals('', $this->fileSystem->getFileExt($x));
        $x = '42';
        $this->assertEquals('', $this->fileSystem->getFileExt($x));
    }


    public function testGetFileName()
    {
        $x = 'a.txt';
        $this->assertEquals('a', $this->fileSystem->getFileName($x));

        $x = 'a.b.c.d.txt';
        $this->assertEquals('a.b.c.d', $this->fileSystem->getFileName($x));

        $x = null;
        $this->assertEquals('', $this->fileSystem->getFileName($x));
        $x = '';
        $this->assertEquals('', $this->fileSystem->getFileName($x));
        $x = '42';
        $this->assertEquals('42', $this->fileSystem->getFileName($x));
    }


    public function testGetFileNameForNew()
    {
        // Prepare a filename
        $name = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
        $name .= $this->utilContainer->get('StringUtil')->random(8);
        $ext = 'ext';
        $file = $name . '.' . $ext;

        // Call 1
        $s1 = $this->fileSystem->getFileNameForNew($file);
        $this->assertEquals($name . '.' . $ext, $s1);
        touch($s1);

        // Call 2
        $s2 = $this->fileSystem->getFileNameForNew($file);
        $this->assertEquals($name . '-1.' . $ext, $s2);
        mkdir($s2);

        // Call 3
        $s3 = $this->fileSystem->getFileNameForNew($file);
        $this->assertEquals($name . '-2.' . $ext, $s3);

        // Cleanup
        unlink($s1);
        rmdir($s2);
    }


    public function testListDir()
    {
        $this->assertTrue(
            0 == count($this->fileSystem->listDir('not-exists-dir', 'mtime', 'asc'))
        );
        $this->assertTrue(
            1 <= count($this->fileSystem->listDir('./', 'mtime', 'asc'))
        );
    }
}
