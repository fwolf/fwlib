<?php
namespace Fwlib\Base\Test;

use Fwlib\Db\CodeDictionary;
use Fwlib\Db\Test\CodeDictionaryDummy;
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
        $this->dict = new CodeDictionaryDummy();

        $this->dbMock = $this->buildDbMock();
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
        $this->assertEquals(null, $this->dict->get(null));
        $this->assertEquals(null, $this->dict->get('notExistKey'));
        $this->assertEquals('a', $this->dict->get(123));
        $this->assertEquals(2, $this->dict->get('bac'));
        $this->assertEquals(
            array(123 => array('title' => 'a'), 321 => array('title' => 'c')),
            $this->dict->Get(array(123, 321))
        );

        $this->assertEquals(
            array('bac' => array('code' => 'bac', 'title' => 2)),
            $this->dict->search('!is_numeric("{code}")')
        );
        $this->assertEquals(
            array(
                123 => array('code' => 123, 'title' => 'a'),
                321 => array('code' => 321, 'title' => 'c')
            ),
            $this->dict->search('"2" == substr("{code}", 1, 1)')
        );
        $this->assertEquals(
            array(
                321 => array('code' => 321, 'title' => 'c')
            ),
            $this->dict->search('"c" == "{title}" && "2" == substr("{code}", 1, 1)')
        );

        // search with assign cols
        $this->assertEquals(
            array(321 => array('title' => 'c')),
            $this->dict->search('"c" == "{title}"', 'title')
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Database not connected
     */
    public function testGetSqlWithDbNotConnected()
    {
        self::$isConnected = false;

        $this->dict->getSql($this->dbMock);
    }


    public function testGetSqlWithNoTableName()
    {
        $this->dict->setTable('');

        $this->assertEmpty($this->dict->getSql(self::$db));
    }


    public function testGetSqlWithTable()
    {
        $this->dict->setTable($this->table);
        if (!self::$db->isTableExist($this->table)) {
            self::$db->execute(
                "CREATE TABLE $this->table (
                    code        CHAR(25)        NOT NULL,
                    title       CHAR(255)       NOT NULL,
                    PRIMARY KEY (code)
                )"
            );
        }
        $sql = $this->dict->getSql(self::$db);

        $this->assertEquals(3, preg_match_all('/INSERT INTO/', $sql, $match));
        $this->assertEquals(1, preg_match_all('/TRUNCATE/', $sql, $match));

        self::$db->execute("DROP TABLE $this->table");
    }


    public function testSet()
    {
        $this->dict = new CodeDictionaryDummy();

        $this->assertEmpty(count($this->dict->search()));

        $this->dict->set(array('foo', 'bar'));
        $this->assertEquals(4, count($this->dict->getAll()));
    }


    public function testSetDelimiter()
    {
        $this->dict = new CodeDictionaryDummy();

        $this->dict->setDelimiter('[[', ']]');

        $this->assertEquals(
            array(321 => array('title' => 'c')),
            $this->dict->search('"c" == "[[title]]"', 'title')
        );

        $this->dict->setDelimiter(':');

        $this->assertEquals(
            array(321 => array('title' => 'c')),
            $this->dict->search('"c" == ":title:"', 'title')
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Primary key value is empty or not set
     */
    public function testSetWithEmptyPk()
    {
        $this->dict->set(array('', 'bar'));
    }


    public function testSetWithEmptyValue()
    {
        $dictBefore = $this->dict->getAll();
        $this->dict->set(array());
        $dictAfter = $this->dict->getAll();

        $this->assertEqualArray($dictBefore, $dictAfter);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage contain all columns
     */
    public function testSetWithEmptyRowInData()
    {
        $this->dict->set(array(array(null), array('foo', 'bar')));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Dictionary column not defined
     */
    public function testSetWithNoColumn()
    {
        $dict = new CodeDictionaryDummy();

        $dict->setColumn(array());

        $dict->set(array('foo' => 'bar'));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage include primary key
     */
    public function testSetWithPrimaryKeyNotInColumn()
    {
        $dict = new CodeDictionaryDummy();

        $dict->setPrimaryKey('notExistColumn');

        $dict->set(array('foo' => 'bar'));
    }
}
