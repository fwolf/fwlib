<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-18
 */
class UtilContainerTest extends PHPunitTestCase
{
    protected function buildMock()
    {
        $utilContainer = $this->getMockBuilder(
            'Fwlib\Util\UtilContainer'
        )
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        return $utilContainer;
    }


    public function testGet()
    {
        $util = $this->buildMock();

        $this->assertEquals(42, $util->get('Array')->getIdx(array(), 'foo', 42));
    }
}
