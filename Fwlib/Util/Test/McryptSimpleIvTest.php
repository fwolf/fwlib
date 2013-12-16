<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\McryptSimpleIv;

/**
 * Test for Fwlib\Util\McryptSimpleIv
 *
 * @requires    extension mcrypt
 *
 * @package     Fwlib\Util\Test
 * @copyright   Copyright 2009-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2009-10-22
 */
class McryptSimpleIvTest extends PHPunitTestCase
{
    public function testMcryptSimpleIv()
    {
        $key = 'blahblahblah';
        $data = '加密的东东';
        $algo = 'xtea';

        $encrypted = McryptSimpleIv::encrypt($data, $key, $algo);
        $this->assertEquals(
            '8vAJEMIdSmH3udoxZ3va',
            base64_encode($encrypted)
        );

        $decrypted = McryptSimpleIv::decrypt($encrypted, $key, $algo);
        $this->assertEquals($data, $decrypted);
    }
}
