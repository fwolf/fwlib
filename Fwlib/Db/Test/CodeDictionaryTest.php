<?php
namespace Fwlib\Base\Test;

use Fwlib\Db\CodeDictionary;
use Fwlib\Db\Test\CodeDictionaryDummy;
use Fwlib\Test\AbstractDbRelateTest;

/**
 * Test for Fwlib\Db\CodeDictionary
 *
 * @package     Fwlib\Db\Test
 * @copyright   Copyright 2011-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2011-07-15
 */
class CodeDictionaryTest extends AbstractDbRelateTest
{
    /**
     * CodeDictionary object
     *
     * @var object
     */
    protected $dict = null;

    /**
     * Table name for generate SQL, need create it for quote name/value
     *
     * @var string
     */
    protected $table = 'test_code_dictionary';

    protected static $dbUsing = 'default';


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dict = new CodeDictionaryDummy();
    }


    public function testGet()
    {
        $this->assertEquals(null, $this->dict->get(null));
        $this->assertEquals(null, $this->dict->get('notExistKey'));
        $this->assertEquals('a', $this->dict->get(123));
        $this->assertEquals(2, $this->dict->get('bac'));
        $this->assertEquals(
            array(123 => 'a', 321 => 'c'),
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
            array(321 => 'c'),
            $this->dict->search('"c" == "{title}"', 'title')
        );
    }


    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testGetSqlWithNoDb()
    {
        $this->dict->getSql(null);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Table not set
     */
    public function testGetSqlWithNoTableName()
    {
        $this->dict->setTable('');
        $this->dict->getSql(self::$db);
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

        $this->assertEquals(3, count($this->dict->search()));

        $this->dict->setPrimaryKey('');
        $this->dict->set(array('foo', 'bar'));
        $this->assertEquals(4, count($this->dict->search()));
    }


    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testSetWithEmptyParam()
    {
        $this->dict->set(null);
    }


    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Data not include dict pk.
     */
    public function testSetWithEmptyPk()
    {
        $this->dict->set(array('', 'bar'));
    }


    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Given data not fit all column.
     */
    public function testSetWithEmptyRowInData()
    {
        $this->dict->set(array(array(null), array('foo', 'bar')));
    }


    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testSetWithNoColumn()
    {
        $dict = new CodeDictionaryDummy();
        $dict->setColumn(array());
        $dict->set(array('foo' => 'bar'));
    }
}
