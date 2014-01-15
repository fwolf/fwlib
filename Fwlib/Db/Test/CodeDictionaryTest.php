<?php
namespace Fwlib\Base\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Db\CodeDictionary;

/**
 * Test for Fwlib\Db\CodeDictionary
 *
 * @package     Fwlib\Db\Test
 * @copyright   Copyright 2011-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2011-07-15
 */
class CodeDictionaryTest extends PHPUnitTestCase
{
    private $db = null;
    private $dict = null;

    /**
     * Db mock return value
     */
    public static $isConnected;
    public static $isDbMysql;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dict = $this->buildMock();

        $this->db = $this->buildDbMock();
    }


    protected function buildDbMock()
    {
        $db = $this->getMockBuilder(
            'Fwlib\Bridge\Adodb'
        )
        ->setMethods(
            array(
                'getProfile', 'getSqlDelimiter', 'getSqlTruncate',
                'getSqlTransBegin', 'getSqlTransCommit',
                'isConnected', 'isDbMysql', 'quoteValue'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $db->expects($this->any())
            ->method('getProfile')
            ->will($this->returnValue(array('lang' => '{profileLang}')));

        $db->expects($this->any())
            ->method('getSqlDelimiter')
            ->will($this->returnValue("{sqlDelimiter}\n"));

        $db->expects($this->any())
            ->method('getSqlTransBegin')
            ->will($this->returnValue("{sqlTransBegin}\n"));

        $db->expects($this->any())
            ->method('getSqlTransCommit')
            ->will($this->returnValue("{sqlTransCommit}\n"));

        $db->expects($this->any())
            ->method('getSqlTruncate')
            ->will($this->returnValue('{sqlTruncate}'));

        $db->expects($this->any())
            ->method('isConnected')
            ->will($this->returnCallback(function () {
                return CodeDictionaryTest::$isConnected;
            }));

        $db->expects($this->any())
            ->method('isDbMysql')
            ->will($this->returnCallback(function () {
                return CodeDictionaryTest::$isDbMysql;
            }));

        $db->expects($this->any())
            ->method('quoteValue')
            ->will($this->returnValue('{quoteValue}'));

        return $db;
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


    public function testFixDictIndex()
    {
        $arrayWithoutIndex = array(
                array(123,  'a'),
                array('bac', 2),
                array(321,  'c'),
        );
        $arrayWithIndex = array(
            123 => array(
                'code'  => 123,
                'title' => 'a',
            ),
            'bac' => array(
                'code'  => 'bac',
                'title' => 2,
            ),
            321 => array(
                'code'  => 321,
                'title' => 'c',
            ),
        );

        $dict = $this->buildMock();

        // Simulate $dict define by assign value to it
        $this->reflectionSet($dict, 'dict', $arrayWithoutIndex);
        // Then call constructor to simulate new operate
        $this->reflectionCall($dict, '__construct');

        $this->assertEqualArray($arrayWithIndex, $dict->getAll());
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

        $dict->getSql($this->db);
    }


    public function testGetSqlWithNoTableName()
    {
        $dict = $this->dict;

        $dict->setTable('');

        $this->assertEmpty($dict->getSql($this->db));
    }


    public function testGetSqlWithTable()
    {
        $dict = $this->dict;

        self::$isConnected = true;
        self::$isDbMysql = true;

        $sqlExpected = 'SET NAMES \'{PROFILELANG}\'{sqlDelimiter}
{sqlTransBegin}
TRUNCATE TABLE code_dictionary{sqlDelimiter}
{sqlTransCommit}
{sqlTransBegin}
INSERT INTO code_dictionary (code, title) VALUES ({quoteValue}, {quoteValue}){sqlDelimiter}
INSERT INTO code_dictionary (code, title) VALUES ({quoteValue}, {quoteValue}){sqlDelimiter}
INSERT INTO code_dictionary (code, title) VALUES ({quoteValue}, {quoteValue}){sqlDelimiter}
{sqlTransCommit}
';

        $sql = $dict->getSql($this->db);

        $this->assertEquals($sqlExpected, $sql);
        $this->assertEquals(3, preg_match_all('/INSERT INTO/', $sql, $match));
        $this->assertEquals(1, preg_match_all('/TRUNCATE/', $sql, $match));
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
