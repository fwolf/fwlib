<?php
namespace Fwlib\Base\Test;

use Fwlib\Db\CodeDictionary;
use Fwlib\Test\AbstractDbRelateTest;

/**
 * Test for Fwlib\Db\CodeDictionary
 *
 * @package     Fwlib\Db\Test
 * @copyright   Copyright 2011-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2011-07-15
 */
class CodeDictionaryTest extends AbstractDbRelateTest
{
    private $dbMock = null;
    private $dict = null;

    /**
     * Table name for generate SQL, need create it for quote name/value
     *
     * @var string
     */
    private $table = 'test_code_dictionary';

    protected static $dbUsing = 'default';

    /**
     * Db mock return value
     */
    public static $isConnected;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dict = $this->buildMock();

        $this->dbMock = $this->buildDbMock();
    }


    protected function buildMock()
    {
        $dict = new CodeDictionary();

        $dict->set(
            array(
                array(123,  'a'),
                array('bac', 2),
                array(321,  'c'),
            )
        );

        return $dict;
    }


    protected function buildDbMock()
    {
        $db = $this->getMockBuilder(
            'Fwlib\Bridge\Adodb'
        )
        ->setMethods(
            array('isConnected')
        )
        ->disableOriginalConstructor()
        ->getMock();

        $db->expects($this->any())
            ->method('isConnected')
            ->will($this->returnCallback(function () {
                return CodeDictionaryTest::$isConnected;
            }));

        return $db;
    }


    public function testGet()
    {
        $dict = $this->dict;

        $this->assertEquals(null, $dict->get(null));
        $this->assertEquals(null, $dict->get('notExistKey'));
        $this->assertEquals('a', $dict->get(123));
        $this->assertEquals(2, $dict->get('bac'));
        $this->assertEquals(
            array(123 => array('title' => 'a'), 321 => array('title' => 'c')),
            $dict->Get(array(123, 321))
        );

        $this->assertEquals(
            array('bac' => array('code' => 'bac', 'title' => 2)),
            $dict->search('!is_numeric("{code}")')
        );
        $this->assertEquals(
            array(
                123 => array('code' => 123, 'title' => 'a'),
                321 => array('code' => 321, 'title' => 'c')
            ),
            $dict->search('"2" == substr("{code}", 1, 1)')
        );
        $this->assertEquals(
            array(
                321 => array('code' => 321, 'title' => 'c')
            ),
            $dict->search('"c" == "{title}" && "2" == substr("{code}", 1, 1)')
        );

        // search with assign cols
        $this->assertEquals(
            array(321 => array('title' => 'c')),
            $dict->search('"c" == "{title}"', 'title')
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Database not connected
     */
    public function testGetSqlWithDbNotConnected()
    {
        $dict = $this->dict;
        self::$isConnected = false;

        $dict->getSql($this->dbMock);
    }


    public function testGetSqlWithNoTableName()
    {
        $dict = $this->dict;

        $dict->setTable('');

        $this->assertEmpty($dict->getSql(self::$db));
    }


    public function testGetSqlWithTable()
    {
        $dict = $this->dict;

        $dict->setTable($this->table);
        if (!self::$db->isTableExist($this->table)) {
            self::$db->execute(
                "CREATE TABLE $this->table (
                    code        CHAR(25)        NOT NULL,
                    title       CHAR(255)       NOT NULL,
                    PRIMARY KEY (code)
                )"
            );
        }
        $sql = $dict->getSql(self::$db);

        $this->assertEquals(3, preg_match_all('/INSERT INTO/', $sql, $match));
        $this->assertEquals(1, preg_match_all('/TRUNCATE/', $sql, $match));

        self::$db->execute("DROP TABLE $this->table");
    }


    public function testSet()
    {
        $dict = $this->dict;

        $this->assertEmpty(count($dict->search()));

        $dict->set(array('foo', 'bar'));
        $this->assertEquals(4, count($dict->getAll()));
    }


    public function testSetDelimiter()
    {
        $dict = $this->dict;

        $dict->setDelimiter('[[', ']]');

        $this->assertEquals(
            array(321 => array('title' => 'c')),
            $dict->search('"c" == "[[title]]"', 'title')
        );

        $dict->setDelimiter(':');

        $this->assertEquals(
            array(321 => array('title' => 'c')),
            $dict->search('"c" == ":title:"', 'title')
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Primary key value is empty or not set
     */
    public function testSetWithEmptyPk()
    {
        $dict = $this->dict;

        $dict->set(array('', 'bar'));
    }


    public function testSetWithEmptyValue()
    {
        $dict = $this->dict;

        $dictBefore = $dict->getAll();
        $dict->set(array());
        $dictAfter = $dict->getAll();

        $this->assertEqualArray($dictBefore, $dictAfter);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage contain all columns
     */
    public function testSetWithEmptyRowInData()
    {
        $dict = $this->dict;

        $dict->set(array(array(null), array('foo', 'bar')));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Dictionary column not defined
     */
    public function testSetWithNoColumn()
    {
        $dict = $this->dict;

        $dict->setColumn(array());

        $dict->set(array('foo' => 'bar'));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage include primary key
     */
    public function testSetWithPrimaryKeyNotInColumn()
    {
        $dict = $this->dict;

        $dict->setPrimaryKey('notExistColumn');

        $dict->set(array('foo' => 'bar'));
    }
}
