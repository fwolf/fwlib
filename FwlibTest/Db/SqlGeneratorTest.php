<?php
namespace FwlibTest\Db;

use Fwlib\Db\SqlGenerator;
use Fwlib\Test\AbstractDbRelateTest;
use Fwlib\Util\Json;

/**
 * Test for Fwlib\Db\SqlGenerator
 *
 * @package     FwlibTest\Db
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-09-09
 */
class SqlGeneratorTest extends AbstractDbRelateTest
{
    protected $sgMysql = null;
    protected $sgSyb = null;

    public function __construct()
    {
        parent::__construct('mysql, sybase');

        if (!self::$dbMysql->isConnected()) {
            $this->markTestSkipped('Mysql db is not connected');
        }

        $this->sgMysql = new SqlGenerator(self::$dbMysql);
        //$this->sgSyb = new SqlGenerator(self::$dbSyb);
    }


    public function testConstruct()
    {
        $sg = new SqlGenerator(self::$dbMysql);

        // Get protected property db from $sg using reflect
        $reflect = new \ReflectionClass($sg);
        $prop = $reflect->getProperty('db');
        $prop->setAccessible(true);
        $db = $prop->getValue($sg);

        $this->assertJsonStringEqualsJsonString(
            Json::encode(self::$dbMysql),
            Json::encode($db)
        );
    }


    public function testEmpty()
    {
        // Error config will got empty result
        $this->sgMysql->clear();
        $ar = array('foo' => 'bar');
        $x = $this->sgMysql->get($ar);
        $this->assertEquals('', $x);

        $x = $this->sgMysql->get(array());
        $this->assertEquals('', $x);
    }


    public function testGetDelete()
    {
        $this->sgMysql->clear();

        // Normal delete
        $ar = array(
            'DELETE'    => self::$tblUser,
            'WHERE'     => 'id = 42',
            'ORDERBY'   => 'credit DESC',
            'LIMIT'     => 1,
            'useless'   => 'blah',
        );
        $x = $this->sgMysql->get($ar);
        $y = 'DELETE FROM ' . self::$tblUser
            . ' WHERE (id = 42) ORDER BY credit DESC LIMIT 1';
        $this->assertEquals($y, $x);

        $x = $this->sgMysql->getDelete($ar);
        $this->assertEquals($y, $x);

        $x = $this->sgMysql->getInsert($ar);
        $this->assertEquals('', $x);


        // Test clear(), get() will re-generate, so use genDelete()
        $this->sgMysql->clear('limit');
        $x = $this->sgMysql->genDelete();
        $y = 'DELETE FROM ' . self::$tblUser
            . ' WHERE (id = 42) ORDER BY credit DESC';
        $this->assertEquals($y, $x);

        // Then clear all part
        $this->sgMysql->clear();
        $x = $this->sgMysql->genDelete();
        $y = '';
        $this->assertEquals($y, $x);
    }


    public function testGetInsert()
    {
        $this->sgMysql->clear();

        // Raw values in config with special char
        $ar = array(
            'INSERT'    => self::$tblUser,
            'VALUES'    => '(credit, title) VALUES ("a\"t\a\'c", 123456)',
        );
        $x = $this->sgMysql->get($ar);
        $y = 'INSERT INTO ' . self::$tblUser
            . '(credit, title) VALUES ("a\"t\a\'c", 123456)';
        $this->assertEquals($y, $x);

        // Raw values in config with special char
        $ar = array(
            'INSERT'    => self::$tblUser,
            'VALUES'    => array(
                'age'  => 123456,
                'title'  => 'string content',
                'joindate'  => date('Y-m-d H:i:s', strtotime('2013-09-17 15:14:50')),
            ),
        );
        $x = $this->sgMysql->get($ar);
        $y = 'INSERT INTO ' . self::$tblUser . '(age, title, joindate) VALUES '
            . '(123456, \'string content\', \'2013-09-17 15:14:50\')';
        $this->assertEquals($y, $x);

        // Default INSERT parts
        $x = $this->sgMysql->genInsert();
        $this->assertEquals($y, $x);

        $x = $this->sgMysql->getInsert($ar);
        $this->assertEquals($y, $x);
    }


    public function testGetPrepared()
    {
        $ar = array(
            'UPDATE'    => self::$tblUser,
            'SET'   => array(
                'credit'    => self::$dbMysql->param('credit'),
            ),
            'WHERE' => 'age = 42',
        );
        $x = $this->sgMysql->getPrepared($ar);
        $y = 'UPDATE ' . self::$tblUser . ' SET credit = ? WHERE (age = 42)';
        $this->assertEquals($y, $x);
    }


    public function testGetSelect()
    {
        $this->sgMysql->clear();

        $ar = array(
            'SELECT'    => 'title, age, credit',
            'FROM'      => self::$tblUser . ' a, ' . self::$tblGroup . ' b',
            'WHERE'     => 'a.uuidGroup = b.uuid',
            'GROUPBY'   => 'b.uuid',
            'HAVING'    => 'a.age > 42',
            'ORDRBY'    => 'a.age DESC',
            'LIMIT'     => 3,
        );
        $x = $this->sgMysql->get($ar);
        $y = 'SELECT title, age, credit FROM ' . self::$tblUser
            . ' a, ' . self::$tblGroup . ' b WHERE (a.uuidGroup = b.uuid) '
            . 'GROUP BY b.uuid HAVING (a.age > 42) LIMIT 3';
        $this->assertEquals($y, $x);

        $this->sgMysql->clear();
        $ar = array(
            'SELECT'    => array(
                'title',
                'titleGroup' => 'b.title'
            ),
            'FROM'      => array(
                'a' => self::$tblUser,
                'b' => self::$tblGroup,
            ),
            'WHERE'     => array(
                'a.uuidGroup = b.uuid',
                '1 = 1',
            ),
            'LIMIT'     => array(1, 3),
        );
        $x = $this->sgMysql->get($ar);
        $y = 'SELECT title, b.title AS \'titleGroup\' FROM ' . self::$tblUser
            . ' a, ' . self::$tblGroup . ' b '
            . 'WHERE (a.uuidGroup = b.uuid) AND (1 = 1) LIMIT 1, 3';
        $this->assertEquals($y, $x);

        // Default SELECT parts
        $x = $this->sgMysql->genSelect();
        $this->assertEquals($y, $x);

        $x = $this->sgMysql->getSelect($ar);
        $this->assertEquals($y, $x);
    }


    public function testGetUpdate()
    {
        $this->sgMysql->clear();

        // Normal update, SQL clause lowercase
        $ar = array(
            'update'    => self::$tblUser,
            'set'       => 'age = 42',
            'where'     => 'credit > 70',
            'orderby'   => 'title desc',
            'limit'     => 1,
        );
        $x = $this->sgMysql->get($ar);
        $y = 'UPDATE ' . self::$tblUser . ' SET age = 42 '
            . 'WHERE (credit > 70) ORDER BY title desc LIMIT 1';
        $this->assertEquals($y, $x);

        // Normal update define with array
        $this->sgMysql->clear();
        $ar = array(
            'update'    => self::$tblUser,
            'set'       => array(
                'age'   => 42,
                'title' => '\'Mr. \' + title',
            ),
            'where'     => array(
                'joindate > \'2013-09-17\'',
                '1 = (age % 2)',   // Odd
            ),
            'orderby'   => array(
                'title desc',
                'joindate asc',
            ),
        );
        $x = $this->sgMysql->get($ar);
        $y = 'UPDATE ' . self::$tblUser . ' SET age = 42, '
            . 'title = \'\\\'Mr. \\\' + title\' '
            . 'WHERE (joindate > \'2013-09-17\') AND (1 = (age % 2)) '
            . 'ORDER BY title desc, joindate asc';
        $this->assertEquals($y, $x);

        // Default UPDATE parts
        $x = $this->sgMysql->genUpdate();
        $this->assertEquals($y, $x);

        $x = $this->sgMysql->getUpdate($ar);
        $this->assertEquals($y, $x);
    }
}
