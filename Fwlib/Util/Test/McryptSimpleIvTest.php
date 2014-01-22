<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\McryptSimpleIv;

/**
 * @requires    extension mcrypt
 *
 * @copyright   Copyright 2009-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2009-10-22
 */
class McryptSimpleIvTest extends PHPunitTestCase
{
    public function testMcryptSimpleIv()
    {
        $mcryptSimpleIv = new McryptSimpleIv;

        $key = 'blahblahblah';
        $data = '加密的东东';
        $algo = 'xtea';

        $encrypted = $mcryptSimpleIv->encrypt($data, $key, $algo);
        $this->assertEquals(
            '8vAJEMIdSmH3udoxZ3va',
            base64_encode($encrypted)
        );

        $decrypted = $mcryptSimpleIv->decrypt($encrypted, $key, $algo);
        $this->assertEquals($data, $decrypted);
    }
}
