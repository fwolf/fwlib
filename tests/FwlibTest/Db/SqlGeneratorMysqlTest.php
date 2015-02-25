<?php
namespace FwlibTest\Db;

use Fwlib\Db\SqlGenerator;
use Fwlib\Test\AbstractDbRelateTest;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SqlGeneratorMysqlTest extends AbstractDbRelateTest
{
    protected static $dbUsing = 'mysql';
    protected $sg = null;

    public function setUp()
    {
        if (is_null($this->sg)) {
            $this->sg = new SqlGenerator(self::$dbMysql);
        }
    }


    public function testConstruct()
    {
        $sg = new SqlGenerator(self::$dbMysql);

        // Get protected property db from $sg using reflect
        $reflect = new \ReflectionClass($sg);
        $prop = $reflect->getProperty('db');
        $prop->setAccessible(true);
        $db = $prop->getValue($sg);

        $json = UtilContainer::getInstance()->get('Json');

        $this->assertJsonStringEqualsJsonString(
            $json->encode(self::$dbMysql),
            $json->encode($db)
        );
    }


    public function testEmpty()
    {
        $this->sg->setUtilContainer(UtilContainer::getInstance());

        // Error config will got empty result
        $this->sg->clear();
        $ar = ['foo' => 'bar'];
        $x = $this->sg->get($ar);
        $this->assertEquals('', $x);

        $x = $this->sg->get([]);
        $this->assertEquals('', $x);
    }


    public function testGetDelete()
    {
        $this->sg->clear();

        // Normal delete
        $ar = [
            'DELETE'    => self::$tableUser,
            'WHERE'     => 'id = 42',
            'ORDERBY'   => 'credit DESC',
            'LIMIT'     => 1,
            'useless'   => 'blah',
        ];
        $x = $this->sg->get($ar);
        $y = 'DELETE FROM ' . self::$tableUser
            . ' WHERE (id = 42) ORDER BY credit DESC LIMIT 1';
        $this->assertEquals($y, $x);

        $x = $this->sg->getDelete($ar);
        $this->assertEquals($y, $x);

        $x = $this->sg->getInsert($ar);
        $this->assertEquals('', $x);


        // Test clear(), get() will re-generate, so use genDelete()
        $this->sg->clear('limit');
        $x = $this->sg->genDelete();
        $y = 'DELETE FROM ' . self::$tableUser
            . ' WHERE (id = 42) ORDER BY credit DESC';
        $this->assertEquals($y, $x);

        // Then clear all part
        $this->sg->clear();
        $x = $this->sg->genDelete();
        $y = '';
        $this->assertEquals($y, $x);
    }


    public function testGetInsert()
    {
        $this->sg->clear();

        // Raw values in config with special char
        $ar = [
            'INSERT'    => self::$tableUser,
            'VALUES'    => '(credit, title) VALUES ("a\"t\a\'c", 123456)',
        ];
        $x = $this->sg->get($ar);
        $y = 'INSERT INTO ' . self::$tableUser
            . '(credit, title) VALUES ("a\"t\a\'c", 123456)';
        $this->assertEquals($y, $x);

        // Raw values in config with special char
        $ar = [
            'INSERT'    => self::$tableUser,
            'VALUES'    => [
                'age'  => 123456,
                'title'  => 'string content',
                'joindate'  => date('Y-m-d H:i:s', strtotime('2013-09-17 15:14:50')),
            ],
        ];
        $x = $this->sg->get($ar);
        $y = 'INSERT INTO ' . self::$tableUser . '(age, title, joindate) VALUES '
            . '(123456, \'string content\', \'2013-09-17 15:14:50\')';
        $this->assertEquals($y, $x);

        // Default INSERT parts
        $x = $this->sg->genInsert();
        $this->assertEquals($y, $x);

        $x = $this->sg->getInsert($ar);
        $this->assertEquals($y, $x);
    }


    public function testGetPrepared()
    {
        $ar = [
            'INSERT'    => self::$tableUser,
            'VALUES'    => [
                'uuid'  => self::$dbMysql->param('uuid'),
                'title' => self::$dbMysql->param('title'),
                'age'   => self::$dbMysql->param('age'),
            ],
        ];
        $x = $this->sg->getPrepared($ar);
        $y = 'INSERT INTO ' . self::$tableUser
            . '(uuid, title, age) VALUES (?, ?, ?)';
        $this->assertEquals($y, $x);

        $ar = [
            'UPDATE'    => self::$tableUser,
            'SET'   => [
                'uuid'      => self::$dbMysql->param('uuid'),
                'title'     => self::$dbMysql->param('title'),
                'credit'    => self::$dbMysql->param('credit'),
            ],
            'WHERE' => 'age = 42',
        ];
        $x = $this->sg->getPrepared($ar);
        $y = 'UPDATE ' . self::$tableUser
            . ' SET uuid = ?, title = ?, credit = ? WHERE (age = 42)';
        $this->assertEquals($y, $x);
    }


    public function testGetSelect()
    {
        $this->sg->clear();

        $ar = [
            'SELECT'    => 'title, age, credit',
            'FROM'      => self::$tableUser . ' a, ' . self::$tableGroup . ' b',
            'WHERE'     => 'a.uuidGroup = b.uuid',
            'GROUPBY'   => 'b.uuid',
            'HAVING'    => 'a.age > 42',
            'ORDRBY'    => 'a.age DESC',
            'LIMIT'     => 3,
        ];
        $x = $this->sg->get($ar);
        $y = 'SELECT title, age, credit FROM ' . self::$tableUser
            . ' a, ' . self::$tableGroup . ' b WHERE (a.uuidGroup = b.uuid) '
            . 'GROUP BY b.uuid HAVING (a.age > 42) LIMIT 3';
        $this->assertEquals($y, $x);

        $this->sg->clear();
        $ar = [
            'SELECT'    => [
                'title',
                'titleGroup' => 'b.title'
            ],
            'FROM'      => [
                'a' => self::$tableUser,
                'b' => self::$tableGroup,
            ],
            'WHERE'     => [
                'a.uuidGroup = b.uuid',
                '1 = 1',
            ],
            'LIMIT'     => [1, 3],
        ];
        $x = $this->sg->get($ar);
        $y = 'SELECT title, b.title AS \'titleGroup\' FROM ' . self::$tableUser
            . ' a, ' . self::$tableGroup . ' b '
            . 'WHERE (a.uuidGroup = b.uuid) AND (1 = 1) LIMIT 1, 3';
        $this->assertEquals($y, $x);

        // Default SELECT parts
        $x = $this->sg->genSelect();
        $this->assertEquals($y, $x);

        $x = $this->sg->getSelect($ar);
        $this->assertEquals($y, $x);
    }


    public function testGetUpdate()
    {
        $this->sg->clear();

        // Normal update, SQL clause lowercase
        $ar = [
            'update'    => self::$tableUser,
            'set'       => 'age = 42',
            'where'     => 'credit > 70',
            'orderby'   => 'title desc',
            'limit'     => 1,
        ];
        $x = $this->sg->get($ar);
        $y = 'UPDATE ' . self::$tableUser . ' SET age = 42 '
            . 'WHERE (credit > 70) ORDER BY title desc LIMIT 1';
        $this->assertEquals($y, $x);

        // Normal update define with array
        $this->sg->clear();
        $ar = [
            'update'    => self::$tableUser,
            'set'       => [
                'age'   => 42,
                'title' => '\'Mr. \' + title',
            ],
            'where'     => [
                'joindate > \'2013-09-17\'',
                '1 = (age % 2)',   // Odd
            ],
            'orderby'   => [
                'title desc',
                'joindate asc',
            ],
        ];
        $x = $this->sg->get($ar);
        $y = 'UPDATE ' . self::$tableUser . ' SET age = 42, '
            . 'title = \'\\\'Mr. \\\' + title\' '
            . 'WHERE (joindate > \'2013-09-17\') AND (1 = (age % 2)) '
            . 'ORDER BY title desc, joindate asc';
        $this->assertEquals($y, $x);

        // Default UPDATE parts
        $x = $this->sg->genUpdate();
        $this->assertEquals($y, $x);

        $x = $this->sg->getUpdate($ar);
        $this->assertEquals($y, $x);
    }
}
