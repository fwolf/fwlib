<?php
namespace FwlibTest\Db;

use Fwlib\Bridge\Adodb;
use Fwlib\Db\AdodbAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AdodbAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | Adodb
     */
    protected function buildAdodbMock()
    {
        $mock = $this->getMockBuilder(
            Adodb::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['isConnected'])
            ->getMock();

        $mock->expects($this->any())
            ->method('isConnected')
            ->will($this->returnValue(true));

        return $mock;
    }


    /**
     * @return MockObject | AdodbAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(
            AdodbAwareTrait::class
        )
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    /**
     * @expectedException \Fwlib\Db\Exception\DbNotConnectedException
     */
    public function testGetWithoutSet()
    {
        $adodbAware = $this->buildMock();

        $this->reflectionCall($adodbAware, 'getDb');
    }


    public function testNormalSetGetDb()
    {
        $adodbAware = $this->buildMock();
        $db = $this->buildAdodbMock();

        $adodbAware->setDb($db);
        $this->assertInstanceOf(
            Adodb::class,
            $this->reflectionCall($adodbAware, 'getDb')
        );
    }
}
