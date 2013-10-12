<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\FileSystem;
use Fwlib\Util\StringUtil;

/**
 * Test for Fwlib\Util\FileSystem
 *
 * @package     Fwlib\Util\Test
 * @copyright   Copyright 2010-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2010-10-10
 */
class FileSystemTest extends PHPunitTestCase
{
    public function testDel()
    {
        // Dir
        $dir = sys_get_temp_dir() . '/test.Fwlib.Util.FileSystem/';
        if (file_exists($dir)) {
            FileSystem::del($dir);
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
        $this->assertEquals(4, FileSystem::getFileSize($foo1));
        $this->assertEquals(
            4,
            FileSystem::getFileSize($dir . 'dir/hardlink-to-foo1')
        );
        $this->assertTrue(FileSystem::getDirSize($foo1, true) > 4);
        $this->assertTrue(
            FileSystem::getDirSize($dir) < FileSystem::getDirSize($dir, true)
        );


        // Test del
        FileSystem::del($dir);
        $this->assertFileNotExists($dir);
    }


    public function testGetDirName()
    {
        $x = 'a/';
        $this->assertEquals('./', FileSystem::getDirName($x));

        $x = 'a/b/c/d';
        $this->assertEquals('a/b/c/', FileSystem::getDirName($x));

        $x = null;
        $this->assertEquals('./', FileSystem::getDirName($x));
        $x = '';
        $this->assertEquals('./', FileSystem::getDirName($x));
        $x = '42';
        $this->assertEquals('./', FileSystem::getDirName($x));
    }


    public function testGetFileExt()
    {
        $x = 'a.txt';
        $this->assertEquals('txt', FileSystem::getFileExt($x));

        $x = 'a.b.c.d.txt';
        $this->assertEquals('txt', FileSystem::getFileExt($x));

        $x = null;
        $this->assertEquals('', FileSystem::getFileExt($x));
        $x = '';
        $this->assertEquals('', FileSystem::getFileExt($x));
        $x = '42';
        $this->assertEquals('', FileSystem::getFileExt($x));
    }


    public function testGetFileName()
    {
        $x = 'a.txt';
        $this->assertEquals('a', FileSystem::getFileName($x));

        $x = 'a.b.c.d.txt';
        $this->assertEquals('a.b.c.d', FileSystem::getFileName($x));

        $x = null;
        $this->assertEquals('', FileSystem::getFileName($x));
        $x = '';
        $this->assertEquals('', FileSystem::getFileName($x));
        $x = '42';
        $this->assertEquals('42', FileSystem::getFileName($x));
    }


    public function testGetNewFile()
    {
        // Prepare a filename
        $name = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
        $name .= StringUtil::random(8);
        $ext = 'ext';
        $file = $name . '.' . $ext;

        // Call 1
        $s1 = FileSystem::getNewFile($file);
        $this->assertEquals($name . '.' . $ext, $s1);
        touch($s1);

        // Call 2
        $s2 = FileSystem::getNewFile($file);
        $this->assertEquals($name . '-1.' . $ext, $s2);
        mkdir($s2);

        // Call 3
        $s3 = FileSystem::getNewFile($file);
        $this->assertEquals($name . '-2.' . $ext, $s3);

        // Cleanup
        unlink($s1);
        rmdir($s2);
    }


    public function testListDir()
    {
        $this->assertTrue(
            0 == count(FileSystem::listDir('not-exists-dir', 'mtime', 'asc'))
        );
        $this->assertTrue(
            1 <= count(FileSystem::listDir('./', 'mtime', 'asc'))
        );
    }
}
