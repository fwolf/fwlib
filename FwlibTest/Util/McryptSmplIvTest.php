<?php
namespace FwlibTest\Util;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\McryptSmplIv;

/**
 * Test for Fwlib\Util\McryptSmplIv
 *
 * @requires    extension mcrypt
 *
 * @package     FwlibTest\Util
 * @copyright   Copyright 2009-2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2009-10-22
 */
class McryptSmplIvTest extends PHPunitTestCase
{
    public function testMcryptSmplIv()
    {
        $key = 'blahblahblah';
        $data = '加密的东东';
        $algo = 'xtea';

        $encrypted = McryptSmplIv::encrypt($data, $key, $algo);
        $this->assertEquals(
            '8vAJEMIdSmH3udoxZ3va',
            base64_encode($encrypted)
        );

        $decrypted = McryptSmplIv::decrypt($encrypted, $key, $algo);
        $this->assertEquals($data, $decrypted);
    }
}
