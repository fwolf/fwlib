<?php
namespace FwlibTest\Web\Helper;

use Fwlib\Web\Helper\TidyTrait;
use FwlibTest\Aide\FunctionMockFactoryAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class TidyTraitTest extends PHPUnitTestCase
{
    use FunctionMockFactoryAwareTrait;


    public static $class_exists = true;
    public static $error_log = '';


    /**
     * @return MockObject | TidyTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(TidyTrait::class)
            ->getMockForTrait();

        return $mock;
    }


    /**
     * @requires extension tidy
     */
    public function testTidy()
    {
        $tidyTrait = $this->buildMock();

        $html = 'foo bar';

        self::$class_exists = false;
        $this->assertEquals(
            $html,
            $this->reflectionCall($tidyTrait, 'tidy', [$html])
        );

        self::$class_exists = true;
        $this->assertStringEndsWith(
            '</html>',
            $this->reflectionCall($tidyTrait, 'tidy', [$html])
        );
    }
}


// Fake function for test
namespace Fwlib\Web\Helper;

function class_exists()
{
    return \FwlibTest\Web\Helper\TidyTraitTest::$class_exists;
}
