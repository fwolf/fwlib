<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\NumberUtil;

/**
 * Test for Fwlib\Util\NumberUtil
 *
 * @package     Fwlib\Util\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-29
 */
class NumberUtilTest extends PHPunitTestCase
{
    public function testToHumanSize()
    {
        $this->assertEquals('100B', NumberUtil::toHumanSize(100));
        $this->assertEquals('1K', NumberUtil::toHumanSize(1001, 1, 1000));
        $this->assertEquals('1.001K', NumberUtil::toHumanSize(1001, 3, 1000));
        $this->assertEquals(
            '52G',
            NumberUtil::toHumanSize(52000000000, 0, 1000)
        );
        // With round
        $this->assertEquals(
            '48.43G',
            NumberUtil::toHumanSize(52000000000, 2, 1024)
        );
        $this->assertEquals(
            '46.185P',
            NumberUtil::toHumanSize(52000000000000000, 3, 1024)
        );
        $this->assertEquals(
            '52000P',
            NumberUtil::toHumanSize(52000000000000000000, 0, 1000)
        );
    }
}
