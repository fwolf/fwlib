<?php
namespace Fwlib\Db\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\ConfigGlobal;
use Fwlib\Db\Test\AbstractDbClientDummy;

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

        // Invalid dbProfile
        $o = new AbstractDbClientDummy('foo');
        $this->assertFalse(isset($o->db));

        if (empty($dbProfile['host'])) {
            $this->markTestSkipped();
        }

        // Use dbProfile and connect when use
        $o = new AbstractDbClientDummy();
        $this->assertFalse(isset($o->db));
        $o->setDbProfile($dbProfile, false);
        $this->assertFalse(isset($o->db));
        $o->db;
        $this->assertTrue(isset($o->db));

        // Need db profile valid
        $o = new AbstractDbClientDummy($dbProfile);
        $this->assertTrue(isset($o->db));

        // For code coverage
        $o->setCharsetPhp('UTF-8');
        $this->assertEquals('UTF-8', $o->db->charsetPhp);

        // Note: Db connect fail not tested, because mysqli_real_connect()
        // will directly print error msg.
    }
}
