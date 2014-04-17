<?php
namespace Fwlib\Base\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Db\CodeDictionary;

/**
 * @copyright   Copyright 2011-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2011-07-15
 */
class CodeDictionaryTest extends PHPUnitTestCase
{
    /**
     * Db mock return value
     */
    public static $isConnected;
    public static $isDbMysql;


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
        $dictionary = new CodeDictionary();

        $dictionary->set(
            array(
                array(123,  'a'),
                array('bac', 2),
                array(321,  'c'),
            )
        );

        return $dictionary;
    }


    public function testFixDictionaryIndex()
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

        $dictionary = $this->buildMock();

        // Simulate $dictionary define by assign value to it
        $this->reflectionSet($dictionary, 'dictionary', $arrayWithoutIndex);
        // Then call constructor to simulate new operate
        $this->reflectionCall($dictionary, '__construct');

        $this->assertEqualArray($arrayWithIndex, $dictionary->getAll());
    }


    public function testGet()
    {
        $dictionary = $this->buildMock();

        $this->assertEquals(null, $dictionary->get(null));
        $this->assertEquals(null, $dictionary->getMultiple(array()));
        $this->assertEquals(null, $dictionary->get('notExistKey'));
        $this->assertEquals(
            array('notExistKey' => null),
            $dictionary->getMultiple(array('notExistKey'))
        );
        $this->assertEquals('a', $dictionary->get(123));
        $this->assertEquals(2, $dictionary->get('bac'));
        $this->assertEquals(
            array(123 => 'a', 321 => 'c'),
            $dictionary->getMultiple(array(123, 321))
        );

        $this->assertEquals(
            array('bac' => array('code' => 'bac', 'title' => 2)),
            $dictionary->search('!is_numeric("{code}")')
        );
        $this->assertEquals(
            array(
                123 => array('code' => 123, 'title' => 'a'),
                321 => array('code' => 321, 'title' => 'c')
            ),
            $dictionary->search('"2" == substr("{code}", 1, 1)')
        );
        $this->assertEquals(
            array(
                321 => array('code' => 321, 'title' => 'c')
            ),
            $dictionary->search(
                '"c" == "{title}" && "2" == substr("{code}", 1, 1)'
            )
        );

        // Search with assign cols
        $this->assertEquals(
            array(321 => 'c'),
            $dictionary->search('"c" == "{title}"', 'title')
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Database not connected
     */
    public function testGetSqlWithDbNotConnected()
    {
        $dictionary = $this->buildMock();
        self::$isConnected = false;

        $dictionary->getSql($this->buildDbMock());
    }


    public function testGetSqlWithNoTableName()
    {
        $dictionary = $this->buildMock();

        $dictionary->setTable('');

        $this->assertEmpty($dictionary->getSql($this->buildDbMock()));
    }


    public function testGetSqlWithTable()
    {
        $dictionary = $this->buildMock();

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

        $sql = $dictionary->getSql($this->buildDbMock());

        $this->assertEquals($sqlExpected, $sql);
        $this->assertEquals(3, preg_match_all('/INSERT INTO/', $sql, $match));
        $this->assertEquals(1, preg_match_all('/TRUNCATE/', $sql, $match));
    }


    public function testSet()
    {
        $dictionary = $this->buildMock();

        $this->assertEmpty(count($dictionary->search()));

        $dictionary->set(array('foo', 'bar'));
        $this->assertEquals(4, count($dictionary->getAll()));
    }


    public function testSetDelimiter()
    {
        $dictionary = $this->buildMock();

        $dictionary->setDelimiter('[[', ']]');

        $this->assertEquals(
            array(321 => 'c'),
            $dictionary->search('"c" == "[[title]]"', 'title')
        );

        $dictionary->setDelimiter(':');

        $this->assertEquals(
            array(321 => 'c'),
            $dictionary->search('"c" == ":title:"', 'title')
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Primary key value is empty or not set
     */
    public function testSetWithEmptyPk()
    {
        $dictionary = $this->buildMock();

        $dictionary->set(array('', 'bar'));
    }


    public function testSetWithEmptyValue()
    {
        $dictionary = $this->buildMock();

        $dictionaryBefore = $dictionary->getAll();
        $dictionary->set(array());
        $dictionaryAfter = $dictionary->getAll();

        $this->assertEqualArray($dictionaryBefore, $dictionaryAfter);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage contain all columns
     */
    public function testSetWithEmptyRowInData()
    {
        $dictionary = $this->buildMock();

        $dictionary->set(array(array(null), array('foo', 'bar')));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Dictionary column not defined
     */
    public function testSetWithNoColumn()
    {
        $dictionary = $this->buildMock();

        $dictionary->setColumns(array());

        $dictionary->set(array('foo' => 'bar'));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage include primary key
     */
    public function testSetWithPrimaryKeyNotInColumn()
    {
        $dictionary = $this->buildMock();

        $dictionary->setPrimaryKey('notExistColumn');

        $dictionary->set(array('foo' => 'bar'));
    }
}
