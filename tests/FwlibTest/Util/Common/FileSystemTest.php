<?php
namespace FwlibTest\Util\Common;

use Fwlib\Util\Common\FileSystem;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\UtilContainerAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2010-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class FileSystemTest extends PHPUnitTestCase
{
    use UtilContainerAwareTrait;


    /**
     * @return FileSystem
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance()->getFileSystem();
    }


    public function testDel()
    {
        $fileSystem = $this->buildMock();

        // Dir
        $dir = sys_get_temp_dir() . '/test.Fwlib.Util.FileSystem/';
        if (file_exists($dir)) {
            $fileSystem->del($dir);
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
        $this->assertEquals(4, $fileSystem->getFileSize($foo1));
        $this->assertEquals(
            4,
            $fileSystem->getFileSize($dir . 'dir/hardlink-to-foo1')
        );
        $this->assertTrue($fileSystem->getDirSize($foo1, true) > 4);
        $this->assertTrue(
            $fileSystem->getDirSize($dir) < $fileSystem->getDirSize($dir, true)
        );


        // Test del
        $fileSystem->del($dir);
        $this->assertFileNotExists($dir);
    }


    public function testGetDirName()
    {
        $fileSystem = $this->buildMock();

        $x = 'a/';
        $this->assertEquals('./', $fileSystem->getDirName($x));

        $x = 'a/b/c/d';
        $this->assertEquals('a/b/c/', $fileSystem->getDirName($x));

        $x = null;
        $this->assertEquals('./', $fileSystem->getDirName($x));
        $x = '';
        $this->assertEquals('./', $fileSystem->getDirName($x));
        $x = '42';
        $this->assertEquals('./', $fileSystem->getDirName($x));
    }


    public function testGetFileExt()
    {
        $fileSystem = $this->buildMock();

        $x = 'a.txt';
        $this->assertEquals('txt', $fileSystem->getFileExt($x));

        $x = 'a.b.c.d.txt';
        $this->assertEquals('txt', $fileSystem->getFileExt($x));

        $x = null;
        $this->assertEquals('', $fileSystem->getFileExt($x));
        $x = '';
        $this->assertEquals('', $fileSystem->getFileExt($x));
        $x = '42';
        $this->assertEquals('', $fileSystem->getFileExt($x));
    }


    public function testGetFileName()
    {
        $fileSystem = $this->buildMock();

        $x = 'a.txt';
        $this->assertEquals('a', $fileSystem->getFileName($x));

        $x = 'a.b.c.d.txt';
        $this->assertEquals('a.b.c.d', $fileSystem->getFileName($x));

        $x = null;
        $this->assertEquals('', $fileSystem->getFileName($x));
        $x = '';
        $this->assertEquals('', $fileSystem->getFileName($x));
        $x = '42';
        $this->assertEquals('42', $fileSystem->getFileName($x));
    }


    public function testGetFileNameForNew()
    {
        $fileSystem = $this->buildMock();

        // Prepare a filename
        $name = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
        $name .= $this->getUtilContainer()->getString()->random(8);
        $ext = 'ext';
        $file = $name . '.' . $ext;

        // Call 1
        $s1 = $fileSystem->getFileNameForNew($file);
        $this->assertEquals($name . '.' . $ext, $s1);
        touch($s1);

        // Call 2
        $s2 = $fileSystem->getFileNameForNew($file);
        $this->assertEquals($name . '-1.' . $ext, $s2);
        mkdir($s2);

        // Call 3
        $s3 = $fileSystem->getFileNameForNew($file);
        $this->assertEquals($name . '-2.' . $ext, $s3);

        // Cleanup
        unlink($s1);
        rmdir($s2);
    }


    public function testListDir()
    {
        $fileSystem = $this->buildMock();

        $this->assertTrue(
            0 == count($fileSystem->listDir('not-exists-dir', 'mtime', 'asc'))
        );
        $this->assertTrue(
            1 <= count($fileSystem->listDir('./', 'mtime', 'asc'))
        );
    }
}
