<?php
namespace FwlibTest\Db;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Db\DbDataExport;
use Fwlib\Test\AbstractDbRelateTest;
use Fwlib\Test\ServiceContainerTest;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class DbDataExportTest extends AbstractDbRelateTest
{
    protected static $dbe = null;
    protected static $dbUsing = 'default';
    protected static $delimiter = '';
    protected static $exportPath = '';
    protected static $insertCount = 23;
    protected static $utilContainer = null;


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


    public function setUp()
    {
        if (is_null(self::$dbe)) {
            self::$dbe = new DbDataExport(self::$db);

            self::$delimiter = $this->reflectionGet(self::$dbe, 'db')
                ->getSqlDelimiter('');

            self::$dbe->setExportPath(self::$exportPath);
        }
    }


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$utilContainer = UtilContainer::getInstance();

        // Generate temp dir name for exported file
        self::$exportPath = tempnam(sys_get_temp_dir(), 'DbDataExportTest-');
        // Unlink tmpfile, will create as dir later
        unlink(self::$exportPath);

        // Insert data for export
        $uuidUtil = self::$utilContainer->get('UuidBase16');
        for ($i = 0; $i < self::$insertCount; $i ++) {
            self::$db->write(
                self::$tableUser,
                [
                    'uuid'  => $uuidUtil->generate(),
                ]
            );
        }
    }


    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        // Remove export path
        self::$utilContainer->get('FileSystem')->del(self::$exportPath);
    }


    public function testConstructWithServiceContainer()
    {
        $dbe = new DbDataExport();
        $dbe->setServiceContainer(ServiceContainerTest::getInstance());
        $this->assertInstanceOf(
            'Fwlib\Bridge\Adodb',
            $this->reflectionCall($dbe, 'getDb')
        );
    }


    public function testExport()
    {
        self::$dbe->setTableExclude(self::$tableGroup);

        //self::$dbe->verbose = true;
        self::$dbe->export();
        $s = file_get_contents(
            self::$exportPath . '/' . self::$tableUser . '.sql'
        );
        $ar = explode("\n", $s);
        //var_dump($ar);
        //echo $s;

        $this->assertFalse(
            file_exists(self::$exportPath . '/' .  self::$tableGroup . '.sql')
        );


        $i = 0;

        $y = 'TRUNCATE TABLE ' . self::$tableUser . self::$delimiter;
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
        self::$utilContainer->get('FileSystem')->del(
            self::$exportPath . DIRECTORY_SEPARATOR . self::$tableUser . '.sql'
        );
    }


    public function testExportWithGroupby()
    {
        self::$dbe->setTableInclude([self::$tableUser]);
        self::$dbe->setTableGroupby(self::$tableUser, 'uuid');

        //self::$dbe->verbose = true;
        self::$dbe->export();
        $s = file_get_contents(
            self::$exportPath . '/' . self::$tableUser . '.sql'
        );
        $ar = explode("\n", $s);
        //var_dump($ar);
        //echo $s;


        $i = 0;

        $y = 'TRUNCATE TABLE ' . self::$tableUser . self::$delimiter;
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
        self::$utilContainer->get('FileSystem')->del(
            self::$exportPath . DIRECTORY_SEPARATOR . self::$tableUser . '.sql'
        );
    }


    public function testExportWithSplitFile()
    {
        self::$dbe->setTableInclude(self::$tableUser);
        self::$dbe->setTableGroupby(self::$tableUser, '');
        self::$dbe->maxRowPerFile = 10;

        //self::$dbe->verbose = true;
        self::$dbe->export();
        //system('ls -l ' . self::$exportPath);
        $s = file_get_contents(
            self::$exportPath . '/' . self::$tableUser . '.2.sql'
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
