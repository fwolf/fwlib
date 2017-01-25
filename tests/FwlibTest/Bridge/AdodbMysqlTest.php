<?php
namespace FwlibTest\Bridge;

use Fwlib\Bridge\Adodb;
use Fwlib\Config\GlobalConfig;
use Fwlib\Db\SqlGenerator;
use Fwlib\Test\AbstractDbRelateTest;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * @copyright   Copyright 2013-2015, 2017 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AdodbMysqlTest extends AbstractDbRelateTest
{
    use UtilContainerAwareTrait;


    protected static $dbUsing = 'mysql';


    public function generateUuid()
    {
        return $this->getUtilContainer()->getUuidBase16()->generate();
    }


    /**
     * Notice: Parameter type in adodb::SetFetchMode() is wrong, version 5.07
     * - 5.19
     */
    public function testCall()
    {
        /** @var \ADOConnection $dbMysql */
        $dbMysql = self::$dbMysql;

        $fetchMode = $dbMysql->fetchMode;
        $dbMysql->setFetchMode(3);    // Both

        $ar = $dbMysql->GetAll('SELECT 1 AS a');
        $y = [['1', 'a' => '1']];
        $this->assertEqualArray($y, $ar);

        $ar = $dbMysql->CacheGetAll(1, 'SELECT 1 AS a');
        $y = [['1', 'a' => '1']];
        $this->assertEqualArray($y, $ar);

        $dbMysql->setFetchMode($fetchMode);
    }


    public function testConstruct()
    {
        $profile = GlobalConfig::getInstance()->get('dbserver.mysql');
        $db = new Adodb($profile);

        $this->assertEqualArray($profile, $db->getProfile());

        // SqlGenerator is not instanced now, see testGenerateSql()
        $this->assertNull($this->reflectionGet($db, 'sqlGenerator'));
    }


    public function testConnect()
    {
        // Clone doesn't affect property object conn
        //$conn = clone self::$dbMysql;

        // Check connection is reused
        $y = self::$dbMysql->_connectionID->thread_id;
        self::$dbMysql->connect(false);
        $x = self::$dbMysql->_connectionID->thread_id;
        $this->assertEquals($y, $x);

        // Force re-connect
        self::$dbMysql->connect(true);
        $x = self::$dbMysql->_connectionID->thread_id;
        $this->assertNotEquals($y, $x);
    }


    public function testConvertEncodingResult()
    {
        // Backup original charset
        $originalCharsetPhp = self::$dbMysql->charsetPhp;
        $originalCharsetDb = self::$dbMysql->profile['lang'];

        self::$dbMysql->setCharsetPhp('UTF-8');
        self::$dbMysql->profile['lang'] = 'GB2312';

        $x = [null, '你好'];
        $y = [null, mb_convert_encoding('你好', 'UTF-8', 'GB2312')];
        $this->assertEquals(
            $y,
            self::$dbMysql->convertEncodingResult($x)
        );


        // Recover original charset
        self::$dbMysql->setCharsetPhp($originalCharsetPhp);
        self::$dbMysql->profile['lang'] = $originalCharsetDb;
    }


    public function testConvertEncodingSql()
    {
        // Backup original charset
        $originalCharsetPhp = self::$dbMysql->charsetPhp;
        $originalCharsetDb = self::$dbMysql->profile['lang'];

        self::$dbMysql->setCharsetPhp('UTF-8');
        self::$dbMysql->profile['lang'] = 'GB2312';

        $x = [null, '你好'];
        $y = [null, mb_convert_encoding('你好', 'GB2312', 'UTF-8')];
        $this->assertEquals(
            $y,
            self::$dbMysql->convertEncodingSql($x)
        );


        // Recover original charset
        self::$dbMysql->setCharsetPhp($originalCharsetPhp);
        self::$dbMysql->profile['lang'] = $originalCharsetDb;
    }


    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testDeleteRow()
    {
        $i = self::$dbMysql->deleteRow(self::$tableUser, '');
        $this->assertEquals(-1, $i);

        $i = self::$dbMysql->deleteRow(
            self::$tableUser,
            'WHERE FALSE'
        );
        $this->assertEquals(0, $i);

        // When use executePrepare(), error was detected and rollback, so
        // return 0 instead of -1, throw exception.
        self::$dbMysql->deleteRow(
            self::$tableUser,
            'WHERE ERROR_CLAUSE'
        );
    }


    public function testError()
    {
        self::$dbMysql->execute('SELECT 1');
        $this->assertEquals('', self::$dbMysql->getErrorMessage());
        $this->assertEquals('', self::$dbMysql->errorMsg());
        $this->assertEquals(0, self::$dbMysql->getErrorCode());
        $this->assertEquals(0, self::$dbMysql->errorNo());
    }


    public function testExecute()
    {
        self::$dbMysql->execute(
            [
                'SELECT'    => 'uuid',
                'FROM'      => self::$tableUser,
                'LIMIT'     => 1
            ]
        );
        $this->assertEquals(0, self::$dbMysql->getErrorCode());
    }


    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testExecutePrepare()
    {
        self::$dbMysql->executePrepare(
            [
                'SELECT'    => 'uuid',
                'FROM'      => self::$tableUser,
                'LIMIT'     => 1
            ]
        );
        $this->assertEquals(0, self::$dbMysql->getErrorCode());

        self::$dbMysql->executePrepare(
            [
                'SELECT'    => 'uuid',
                'FROM'      => self::$tableUser,
                'WHERE'     => 'Error Clause',
            ]
        );
    }


    /**
     * Test for Mysql db only
     */
    public function testForMysqlOnly()
    {
        if (!self::$dbMysql->isDbMysql()) {
            $this->markTestSkipped('Skip mysql only test.');
        }

        $this->assertEquals(
            ";\n",
            self::$dbMysql->getSqlDelimiter()
        );

        $this->assertEquals(
            false,
            self::$dbMysql->isTimestampUnique()
        );
    }


    public function testGenerateSql()
    {
        $userTable = self::$tableUser;

        $x = self::$dbMysql->generateSql('');
        $this->assertEquals('', $x);


        // SqlGenerator is instanced now
        $this->assertInstanceOf(
            SqlGenerator::class,
            $this->reflectionGet(self::$dbMysql, 'sqlGenerator')
        );


        $ar = [
            'SELECT'    => 'title',
            'FROM'      => $userTable,
        ];
        $x = self::$dbMysql->generateSql($ar);
        $y = "SELECT title FROM $userTable";
        $this->assertEquals($y, $x);
    }


    public function testGenerateSqlPrepared()
    {
        $userTable = self::$tableUser;

        $x = self::$dbMysql->generateSqlPrepared('');
        $this->assertEquals('', $x);

        $ar = [
            'INSERT'    => self::$tableUser,
            'VALUES'    => [
                'uuid'  => self::$dbMysql->param('uuid'),
                'title' => self::$dbMysql->param('title'),
                'age'   => self::$dbMysql->param('age'),
            ],
        ];
        $x = self::$dbMysql->generateSqlPrepared($ar);
        $y = "INSERT INTO $userTable(uuid, title, age) VALUES (?, ?, ?)";
        $this->assertEquals($y, $x);
    }


    /**
     * @expectedException \PHPUnit_Framework_Error_Warning
     */
    public function testGetByKey()
    {
        // Normal getByKey() tested with write()

        // Prepare data
        $uuid = $this->generateUuid();
        $ar = [
            'uuid'  => $uuid,
            'title' => 'Title',
            'age'   => 42,
        ];
        self::$dbMysql->write(self::$tableUser, $ar);


        // Different way to read one column
        $this->assertEquals(
            'Title',
            self::$dbMysql->getByKey(self::$tableUser, $uuid, 'title')
        );
        $this->assertEquals(
            'Title',
            self::$dbMysql->getByKey(self::$tableUser, $uuid, 'title', 'uuid')
        );
        $this->assertEquals(
            'Title',
            self::$dbMysql->getByKey(
                self::$tableUser,
                [$uuid],
                'title',
                ['uuid']
            )
        );

        // Read more than one column
        $this->assertEqualArray(
            ['title' => 'Title', 'age' => '42'],
            self::$dbMysql->getByKey(self::$tableUser, $uuid, 'title, age')
        );

        // * col
        $data = self::$dbMysql->getByKey(self::$tableUser, $uuid);
        $this->assertEquals('Title', $data['title']);
        $this->assertEquals(42, $data['age']);

        // Not exists data
        $data = self::$dbMysql->getByKey(self::$tableUser, $uuid . 'foo');
        $this->assertEquals(null, $data);

        // More PK value than column, throw exception
        $this->assertEquals(
            null,
            self::$dbMysql->getByKey(self::$tableUser, [1, 2], 'title')
        );
    }


    public function testGetMetaPrimaryKey()
    {
        // Normal test completed by other method

        $this->assertEquals(
            null,
            self::$dbMysql->getMetaPrimaryKey(self::$tableUser . '_not_exists')
        );
    }


    public function testGetMetaTimestamp()
    {
        $this->assertEquals(
            '',
            self::$dbMysql->getMetaTimeStamp(self::$tableUser . '_not_exists')
        );

        $this->assertEquals(
            '',
            self::$dbMysql->getMetaTimeStamp(self::$tableGroup)
        );
    }


    public function testGetProfileString()
    {
        $profileString = self::$dbMysql->getProfileString('-');
        $this->assertEquals(2, substr_count($profileString, '-'));
    }


    public function testGetQueryCount()
    {
        /** @var \ADOConnection|\Fwlib\Bridge\Adodb $db */
        $db = self::$dbMysql;
        $i = $db->getQueryCount();

        $db->GetAll('SELECT 1 AS a');
        $this->assertEquals($i + 1, $db->getQueryCount());

        $db->CacheGetAll(0, 'SELECT 1 AS a');
        $this->assertEquals($i + 1, $db->getQueryCount());

        $db->GetAll('SELECT 1 AS a');
        $this->assertEquals($i + 2, $db->getQueryCount());
    }


    public function testGetSet()
    {
        self::$dbMysql->debug = false;
        $this->assertFalse(self::$dbMysql->debug);
    }


    public function testGetSqlTrans()
    {
        $this->assertEquals(
            'TRANSACTION',
            substr(self::$dbMysql->getSqlTransBegin(), 6, 11)
        );
        $this->assertStringStartsWith(
            'COMMIT',
            self::$dbMysql->getSqlTransCommit()
        );
        $this->assertStringStartsWith(
            'ROLLBACK',
            self::$dbMysql->getSqlTransRollback()
        );
    }


    public function testIsTblExist()
    {
        $this->assertTrue(self::$dbMysql->isTableExist(self::$tableUser));
        $this->assertFalse(
            self::$dbMysql->isTableExist(self::$tableUser . '_not_exists')
        );
    }


    /**
     * @expectedException \PHPUnit_Framework_Error_Warning
     */
    public function testQuoteValue()
    {
        // Normal test completed by other method

        // Quote un-exists column
        // Compare will not execute because exception will be caught by PHPUnit
        $col = 'not_exists';
        $this->assertEquals(
            '\'' . $col . '\'',
            self::$dbMysql->quoteValue(self::$tableUser, $col, $col)
        );
    }


    /**
     * @expectedException \PHPUnit_Framework_Error_Warning
     */
    public function testWrite()
    {
        $uuid = $this->generateUuid();

        // Auto INSERT
        $ar = [
            'uuid'  => $uuid,
            'title' => 'Title',
            'age'   => 42,
        ];
        self::$dbMysql->write(self::$tableUser, $ar);
        $this->assertEquals(
            'Title',
            self::$dbMysql->getByKey(self::$tableUser, $uuid, 'title')
        );

        // Auto UPDATE
        $ar['age'] = 24;
        self::$dbMysql->write(self::$tableUser, $ar);
        $this->assertEquals(
            24,
            self::$dbMysql->getByKey(self::$tableUser, $uuid, 'age')
        );

        // Write without PK, will fail
        unset($ar['uuid']);
        $this->assertEquals(
            -1,
            self::$dbMysql->write(self::$tableUser, $ar)
        );

        // For INSERT, will fail and throw exception
        $ar['uuid'] = $uuid;
        $this->assertEquals(
            -1,
            self::$dbMysql->write(self::$tableUser, $ar, 'I')
        );
    }
}
