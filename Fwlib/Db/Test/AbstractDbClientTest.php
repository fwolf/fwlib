<?php
namespace Fwlib\Db\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\ConfigGlobal;
use Fwlib\Db\Test\AbstractDbClientDummy;
use Fwlib\Test\ServiceContainerTest as ServiceContainerTest;

/**
 * Test for Fwlib\Db\AbstractDbClient
 *
 * @package     Fwlib\Db\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-10-08
 */
class AbstractDbClientTest extends PHPunitTestCase
{
    public function testSetDbProfile()
    {
        $dbProfile = ConfigGlobal::get('dbserver.default');
        if (empty($dbProfile['host'])) {
            $this->markTestSkipped();
        }

        // Use dbProfile and connect when use
        $o = new AbstractDbClientDummy(ServiceContainerTest::getInstance());
        $this->assertFalse(isset($o->db));
        $o->db;
        $this->assertTrue(isset($o->db));
    }
}
