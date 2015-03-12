<?php
namespace FwlibTest\Base;

use Fwlib\Base\ClassLoader;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ClassLoaderTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ClassLoader
     */
    protected function buildMock()
    {
        $mock = $this->getMock(
            ClassLoader::class,
            null
        );

        return $mock;
    }


    public function testFindFile()
    {
        $classLoader = $this->buildMock();
        // Tailing '/' will automatic be added
        $pathToFwlib = __DIR__ . '/../../../src/Fwlib';


        // Normal class with namespace, PSR-4 style
        $classLoader->addPrefix('Fwlib', "{$pathToFwlib}");
        $this->assertEquals(
            "{$pathToFwlib}/Util/Common/DatetimeUtil.php",
            $classLoader->findFile('Fwlib\Util\Common', 'DatetimeUtil')
        );

        $this->assertFalse(
            $classLoader->findFile('Fwlib\Util\Common', 'NotExistsClass')
        );


        // Class without namespace
        $classLoader->addPrefix(
            'ReturnValue',
            "{$pathToFwlib}/Base/ReturnValue.php"
        );
        $this->assertEquals(
            "{$pathToFwlib}/Base/ReturnValue.php",
            $classLoader->findFile('ReturnValue', '')
        );

        $this->assertFalse(
            $classLoader->findFile('NotExistsClass', '')
        );
    }
}
