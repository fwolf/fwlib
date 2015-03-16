<?php
namespace FwlibTest\Base;

use Fwlib\Bridge\Adodb;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Db\CodeDictionary;

/**
 * @copyright   Copyright 2011-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
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
            Adodb::class
        )
        ->setMethods(
            [
                'getProfile', 'getSqlDelimiter', 'getSqlTruncate',
                'getSqlTransBegin', 'getSqlTransCommit',
                'isConnected', 'isDbMysql', 'quoteValue'
            ]
        )
        ->disableOriginalConstructor()
        ->getMock();

        $db->expects($this->any())
            ->method('getProfile')
            ->will($this->returnValue(['lang' => '{profileLang}']));

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
            [
                [123,  'a'],
                ['bac', 2],
                [321,  'c'],
            ]
        );

        return $dictionary;
    }


    public function testFixDictionaryIndex()
    {
        $arrayWithoutIndex = [
                [123,  'a'],
                ['bac', 2],
                [321,  'c'],
        ];
        $arrayWithIndex = [
            123 => [
                'code'  => 123,
                'title' => 'a',
            ],
            'bac' => [
                'code'  => 'bac',
                'title' => 2,
            ],
            321 => [
                'code'  => 321,
                'title' => 'c',
            ],
        ];

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
        $this->assertEquals(null, $dictionary->getMultiple([]));
        $this->assertEquals(null, $dictionary->get('notExistKey'));
        $this->assertEquals(
            ['notExistKey' => null],
            $dictionary->getMultiple(['notExistKey'])
        );
        $this->assertEquals('a', $dictionary->get(123));
        $this->assertEquals(2, $dictionary->get('bac'));
        $this->assertEquals(
            [123 => 'a', 321 => 'c'],
            $dictionary->getMultiple([123, 321])
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

        /** @noinspection SpellCheckingInspection */
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


    public function testSearch()
    {
        $dictionary = $this->buildMock();

        $this->assertEquals(
            ['bac' => ['code' => 'bac', 'title' => 2]],
            $dictionary->search(function ($row) {
                return !is_numeric($row['code']);
            })
        );

        $this->assertEquals(
            [
                123 => ['code' => 123, 'title' => 'a'],
                321 => ['code' => 321, 'title' => 'c']
            ],
            $dictionary->search(function ($row) {
                return '2' == substr($row['code'], 1, 1);
            })
        );

        $this->assertEquals(
            [
                321 => ['code' => 321, 'title' => 'c']
            ],
            $dictionary->search(function ($row) {
                return 'c' == $row['title'] &&
                    '2' == substr($row['code'], 1, 1);
            })
        );

        // Search with assign cols
        $this->assertEquals(
            [321 => 'c'],
            $dictionary->search(function ($row) {
                return 'c' == $row['title'];
            }, 'title')
        );

        // Search on empty dictionary will return empty array
        $this->reflectionSet($dictionary, 'dictionary', []);
        $this->assertEqualArray(
            [],
            $dictionary->search('time')
        );
    }


    public function testSet()
    {
        $dictionary = $this->buildMock();

        $dictionary->set(['foo', 'bar']);
        $this->assertEquals(4, count($dictionary->getAll()));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Primary key value is empty or not set
     */
    public function testSetWithEmptyPk()
    {
        $dictionary = $this->buildMock();

        $dictionary->set(['', 'bar']);
    }


    public function testSetWithEmptyValue()
    {
        $dictionary = $this->buildMock();

        $dictionaryBefore = $dictionary->getAll();
        $dictionary->set([]);
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

        $dictionary->set([[null], ['foo', 'bar']]);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Dictionary column not defined
     */
    public function testSetWithNoColumn()
    {
        $dictionary = $this->buildMock();

        $dictionary->setColumns([]);

        $dictionary->set(['foo' => 'bar']);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage include primary key
     */
    public function testSetWithPrimaryKeyNotInColumn()
    {
        $dictionary = $this->buildMock();

        $dictionary->setPrimaryKey('notExistColumn');

        $dictionary->set(['foo' => 'bar']);
    }
}
