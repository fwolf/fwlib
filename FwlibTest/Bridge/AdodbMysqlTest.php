<?php
namespace FwlibTest\Bridge;

use Fwlib\Bridge\Adodb;
use Fwlib\Config\ConfigGlobal;
use Fwlib\Test\AbstractDbRelateTest;
use Fwlib\Util\Uuid;

/**
 * Test for Fwlib\Bridge\Adodb
 *
 * @package     FwlibTest\Bridge
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-09-18
 */
class AdodbMysqlTest extends AbstractDbRelateTest
{
    protected $dbUsing = 'mysql';


    public function testCall()
    {
        $ar = self::$dbMysql->GetAll('SELECT 1 AS a');
        $y = array(array('1', 'a' => '1'));
        $this->assertEqualArray($y, $ar);

        $ar = self::$dbMysql->CacheGetAll(1, 'SELECT 1 AS a');
        $y = array(array('1', 'a' => '1'));
        $this->assertEqualArray($y, $ar);
    }


    public function testConstruct()
    {
        $db = new Adodb(ConfigGlobal::get('dbserver.mysql'));

        $this->assertFalse(isset($db->sqlGenerator));
        // Will auto create SqlGenerator when access
        $this->assertFalse(is_null($db->sqlGenerator));
        $this->assertTrue(isset($db->sqlGenerator));
    }


    public function testCheckTblExist()
    {
        $this->assertTrue(self::$dbMysql->checkTblExist(self::$tblUser));
        $this->assertFalse(
            self::$dbMysql->checkTblExist(self::$tblUser . '_not_exists')
        );
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


    public function testConvertEncodingRs()
    {
        // Backup original charset
        $originalCharsetSys = self::$dbMysql->charsetSys;
        $originalCharsetDb = self::$dbMysql->dbProfile['lang'];

        self::$dbMysql->charsetSys = 'utf-8';
        self::$dbMysql->dbProfile['lang'] = 'gb2312';

        $x = array(null, '你好');
        $y = array(null, mb_convert_encoding('你好', 'utf-8', 'gb2312'));
        $this->assertEquals(
            $y,
            self::$dbMysql->convertEncodingRs($x)
        );


        // Recover original charset
        self::$dbMysql->charsetSys = $originalCharsetSys;
        self::$dbMysql->dbProfile['lang'] = $originalCharsetDb;
    }


    public function testConvertEncodingSql()
    {
        // Backup original charset
        $originalCharsetSys = self::$dbMysql->charsetSys;
        $originalCharsetDb = self::$dbMysql->dbProfile['lang'];

        self::$dbMysql->charsetSys = 'utf-8';
        self::$dbMysql->dbProfile['lang'] = 'gb2312';

        $x = array(null, '你好');
        $y = array(null, mb_convert_encoding('你好', 'gb2312', 'utf-8'));
        $this->assertEquals(
            $y,
            self::$dbMysql->convertEncodingSql($x)
        );


        // Recover original charset
        self::$dbMysql->charsetSys = $originalCharsetSys;
        self::$dbMysql->dbProfile['lang'] = $originalCharsetDb;
    }


    public function testCountQuery()
    {
        $db = self::$dbMysql;
        $i = $db::$queryCount;

        $db->GetAll('SELECT 1 AS a');
        $this->assertEquals($i + 1, $db::$queryCount);

        $db->CacheGetAll(0, 'SELECT 1 AS a');
        $this->assertEquals($i + 1, $db::$queryCount);

        $db->GetAll(0, 'SELECT 1 AS a');
        $this->assertEquals($i + 2, $db::$queryCount);
    }


    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testDelRow()
    {
        $i = self::$dbMysql->delRow(self::$tblUser, '');
        $this->assertEquals(-1, $i);

        $i = self::$dbMysql->delRow(
            self::$tblUser,
            'WHERE FALSE'
        );
        $this->assertEquals(0, $i);

        // When use executePrepare(), error was detected and rollback, so
        // return 0 instead of -1, throw exception.
        self::$dbMysql->delRow(
            self::$tblUser,
            'WHERE ERROR_CLAUSE'
        );
    }


    public function testError()
    {
        self::$dbMysql->execute('SELECT 1');
        $this->assertEquals('', self::$dbMysql->errorMsg());
        $this->assertEquals(0, self::$dbMysql->errorNo());
    }


    public function testExecute()
    {
        self::$dbMysql->execute(
            array(
                'SELECT'    => 'uuid',
                'FROM'      => self::$tblUser,
                'LIMIT'     => 1
            )
        );
        $this->assertEquals(0, self::$dbMysql->errorNo(0));
    }


    public function testExecuteGenSql()
    {
        if (!method_exists(self::$dbMysql, 'executeGenSql')) {
            $this->markTestSkipped('Adodb::executeGenSql() not exists.');
        }

        self::$dbMysql->executeGenSql(
            array(
                'SELECT'    => 'uuid',
                'FROM'      => self::$tblUser,
                'LIMIT'     => 1
            )
        );
        $this->assertEquals(0, self::$dbMysql->errorNo(0));
    }


    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testExecutePrepare()
    {
        self::$dbMysql->executePrepare(
            array(
                'SELECT'    => 'uuid',
                'FROM'      => self::$tblUser,
                'LIMIT'     => 1
            )
        );
        $this->assertEquals(0, self::$dbMysql->errorNo(0));

        self::$dbMysql->executePrepare(
            array(
                'SELECT'    => 'uuid',
                'FROM'      => self::$tblUser,
                'WHERE'     => 'Error Clause',
            )
        );
    }


    public function testFindColTs()
    {
        $this->assertEquals(
            '',
            self::$dbMysql->findColTs(self::$tblUser . '_not_exists')
        );

        $this->assertEquals(
            '',
            self::$dbMysql->findColTs(self::$tblGroup)
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
            self::$dbMysql->isTsUnique()
        );
    }


    public function testGenSql()
    {
        $x = self::$dbMysql->genSql('');
        $this->assertEquals('', $x);

        $ar = array(
            'SELECT'    => 'title',
            'FROM'      => self::$tblUser,
        );
        $x = self::$dbMysql->genSql($ar);
        $y = 'SELECT title FROM ' . self::$tblUser;
        $this->assertEquals($y, $x);
    }


    public function testGenSqlPrepared()
    {
        $x = self::$dbMysql->genSqlPrepared('');
        $this->assertEquals('', $x);

        $ar = array(
            'INSERT'    => self::$tblUser,
            'VALUES'    => array(
                'uuid'  => self::$dbMysql->param('uuid'),
                'title' => self::$dbMysql->param('title'),
                'age'   => self::$dbMysql->param('age'),
            ),
        );
        $x = self::$dbMysql->genSqlPrepared($ar);
        $y = 'INSERT INTO ' . self::$tblUser
            . '(uuid, title, age) VALUES (?, ?, ?)';
        $this->assertEquals($y, $x);
    }


    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testGetByPk()
    {
        // Normal getByPk() tested with write()

        // Prepare data
        $uuid = Uuid::gen();
        $ar = array(
            'uuid'  => $uuid,
            'title' => 'Title',
            'age'   => 42,
        );
        self::$dbMysql->write(self::$tblUser, $ar);


        // * col
        $data = self::$dbMysql->getByPk(self::$tblUser, $uuid);
        $this->assertEquals('Title', $data['title']);
        $this->assertEquals(42, $data['age']);

        // Not exists data
        $data = self::$dbMysql->getByPk(self::$tblUser, $uuid . 'foo');
        $this->assertEquals(null, $data);

        // More PK value than column, throw exception
        $this->assertEquals(
            null,
            self::$dbMysql->getByPk(self::$tblUser, array(1, 2), 'title')
        );
    }


    public function testGetMetaPrimaryKey()
    {
        // Normal test completed by other method

        $this->assertEquals(
            null,
            self::$dbMysql->getMetaPrimaryKey(self::$tblUser . '_not_exists')
        );
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


    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testQuoteValue()
    {
        // Normal test completed by other method

        // Quote un-exists column
        // Compare will not execute because exception catched by PHPUnit
        $col = 'not_exists';
        $this->assertEquals(
            '\'' . $col . '\'',
            self::$dbMysql->quoteValue(self::$tblUser, $col, $col)
        );
    }


    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testWrite()
    {
        $uuid = Uuid::gen();

        // Auto INSERT
        $ar = array(
            'uuid'  => $uuid,
            'title' => 'Title',
            'age'   => 42,
        );
        self::$dbMysql->write(self::$tblUser, $ar);
        $this->assertEquals(
            'Title',
            self::$dbMysql->getByPk(self::$tblUser, $uuid, 'title')
        );

        // Auto UPDATE
        $ar['age'] = 24;
        self::$dbMysql->write(self::$tblUser, $ar);
        $this->assertEquals(
            24,
            self::$dbMysql->getByPk(self::$tblUser, $uuid, 'age')
        );

        // Write without PK, will fail
        unset($ar['uuid']);
        $this->assertEquals(
            -1,
            self::$dbMysql->write(self::$tblUser, $ar)
        );

        // For INSERT, will fail and throw exception
        $ar['uuid'] = $uuid;
        $this->assertEquals(
            -1,
            self::$dbMysql->write(self::$tblUser, $ar, 'I')
        );
    }
}
