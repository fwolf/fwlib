<?php
namespace FwlibTest\Db;

use Fwlib\Db\KeyValueDictionary;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class KeyValueDictionaryTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|KeyValueDictionary
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(KeyValueDictionary::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }


    public function testFixDictionaryIndex()
    {
        $dict = $this->buildMock();

        $this->reflectionSet($dict, 'dictionary', [
            'foo' => 21,
            'bar' => 42,
        ]);
        $this->reflectionCall($dict, 'fixDictionaryIndex');

        $dictAr = $this->reflectionGet($dict, 'dictionary');
        $this->assertEqualArray($dictAr, [
            'foo' => [
                'code'  => 'foo',
                'title' => 21,
            ],
            'bar' => [
                'code'  => 'bar',
                'title' => 42,
            ],
        ]);
    }


    public function testGetSingleColumn()
    {
        $dict = $this->buildMock();
        $dict->set([
            ['foo', 21],
            ['bar', 42],
        ]);

        $this->assertEqualArray($dict->getSingleColumn(), [
            'foo' => 21,
            'bar' => 42,
        ]);
    }
}
