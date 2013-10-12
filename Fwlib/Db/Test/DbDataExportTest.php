<?php
namespace Fwlib\Db\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\ConfigGlobal;
use Fwlib\Db\DbDataExport;
use Fwlib\Test\AbstractDbRelateTest;
use Fwlib\Util\FileSystem;
use Fwlib\Util\Uuid;

/**
 * Test for Fwlib\Db\DbDataExport
 *
 * @package     Fwlib\Db\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-10-11
 */
class DbDataExportTest extends AbstractDbRelateTest
{
    protected static $dbe = null;
    protected static $dbUsing = 'default';
    protected static $delimiter = '';
    protected static $exportPath = '';
    protected static $insertCount = 23;


    /**
     * Contructor may run multiple times(N of testMethod()) because PHPUnit
     * use Reflection on it, so put init script to setUpBeforeClass(), which
     * will only run once. Also, $dbe and $delimiter must be static to share
     * between testMethod().
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function newObjDbe()
    {
        if (is_null(self::$dbe)) {
            self::$dbe = new DbDataExport(self::$db->dbProfile);

            self::$delimiter = self::$dbe->db->getSqlDelimiter('');

            self::$dbe->setExportPath(self::$exportPath);
        }
    }


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // Create temp dir for exported file
        self::$exportPath = tempnam(sys_get_temp_dir(), 'DbDataExportTest-');
        // Unlink tmpfile, will create as dir later
        unlink(self::$exportPath);

        // Insert data for export
        for ($i = 0; $i < self::$insertCount; $i ++) {
            self::$db->write(
                self::$tblUser,
                array(
                    'uuid'  => Uuid::gen(),
                )
            );
        }
    }


    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        // Remove export path
        FileSystem::del(self::$exportPath);
    }


    public function testExport()
    {
        $this->newObjDbe();

        self::$dbe->setTableExclude(self::$tblGroup);

        //self::$dbe->verbose = true;
        self::$dbe->export();
        $s = file_get_contents(
            self::$exportPath . '/' . self::$tblUser . '.sql'
        );
        $ar = explode("\n", $s);
        //var_dump($ar);
        //echo $s;

        $this->assertFalse(
            file_exists(self::$exportPath . '/' .  self::$tblGroup . '.sql')
        );


        $i = 0;

        $y = 'TRUNCATE TABLE ' . self::$tblUser . self::$delimiter;
        $this->assertEquals($y, $ar[$i++]);

        // Skip 'INSERT' line
        $ref = new \ReflectionMethod(self::$dbe, 'needIdentityInsert');
        $ref->setAccessible(true);
        if ($ref->invoke(self::$dbe)) {
            $i ++;
        }

        $i += 2;
        $y = ')' . self::$delimiter;
        $this->assertEquals($y, $ar[$i++]);

        $i += 2;
        $this->assertEquals($y, $ar[$i++]);

        // Last line of last sql
        $i += (self::$insertCount - 2) * 3 - 1;
        $this->assertEquals($y, $ar[$i++]);

        // Clean
        FileSystem::del(
            self::$exportPath . DIRECTORY_SEPARATOR . self::$tblUser . '.sql'
        );
    }


    public function testExportWithGroupby()
    {
        $this->newObjDbe();

        self::$dbe->setTableInclude(array(self::$tblUser));
        self::$dbe->setTableGroupby(self::$tblUser, 'uuid');

        //self::$dbe->verbose = true;
        self::$dbe->export();
        $s = file_get_contents(
            self::$exportPath . '/' . self::$tblUser . '.sql'
        );
        $ar = explode("\n", $s);
        //var_dump($ar);
        //echo $s;


        $i = 0;

        $y = 'TRUNCATE TABLE ' . self::$tblUser . self::$delimiter;
        $this->assertEquals($y, $ar[$i++]);

        // Skip 'INSERT' line
        $ref = new \ReflectionMethod(self::$dbe, 'needIdentityInsert');
        $ref->setAccessible(true);
        if ($ref->invoke(self::$dbe)) {
            $i ++;
        }

        $i += 2;
        $y = ')' . self::$delimiter;
        $this->assertEquals($y, $ar[$i++]);

        $i += 2;
        $this->assertEquals($y, $ar[$i++]);

        // Last line of last sql
        $i += (self::$insertCount - 2) * 3 - 1;
        $this->assertEquals($y, $ar[$i++]);

        // Clean
        FileSystem::del(
            self::$exportPath . DIRECTORY_SEPARATOR . self::$tblUser . '.sql'
        );
    }


    public function testExportWithSplitFile()
    {
        $this->newObjDbe();

        self::$dbe->setTableInclude(self::$tblUser);
        self::$dbe->setTableGroupby(self::$tblUser, '');
        self::$dbe->maxRowPerFile = 10;

        //self::$dbe->verbose = true;
        self::$dbe->export();
        //system('ls -l ' . self::$exportPath);
        $s = file_get_contents(
            self::$exportPath . '/' . self::$tblUser . '.2.sql'
        );
        $ar = explode("\n", $s);
        //var_dump($ar);
        //echo $s;


        $i = 0;

        // No header in last sql file
        $i += 2;
        $y = ')' . self::$delimiter;
        $this->assertEquals($y, $ar[$i++]);

        // Last line of last sql
        $i += ((self::$insertCount % self::$dbe->maxRowPerFile) - 1) * 3 - 1;
        $this->assertEquals($y, $ar[$i++]);

        // Clear is done in tearDownAfterClass()
    }


    public function testSetExportPath()
    {
        $this->newObjDbe();

        // $exportPath is set and exists, assign same value again will direct
        // return true.
        $this->assertTrue(self::$dbe->setExportPath(self::$exportPath));


        // Assign $exportPath to a exists file.
        $pathBackup = self::$dbe->exportPath;

        $path = tempnam(sys_get_temp_dir(), 'DbDataExportTest-');
        $this->expectOutputString(
            'Export target path is a file.' . PHP_EOL
        );
        self::$dbe->setExportPath($path);

        unlink($path);

        self::$dbe->exportPath = $pathBackup;
    }
}
