<?php
namespace FwlibTest\Util\Algorithm;

use Fwlib\Util\Algorithm\McryptSimpleIv;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @requires    extension mcrypt
 *
 * @copyright   Copyright 2009-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class McryptSimpleIvTest extends PHPUnitTestCase
{
    public function testMcryptSimpleIv()
    {
        $mcryptSimpleIv = new McryptSimpleIv;

        $key = 'FooBar';
        $data = '加密的东东';
        $algorithm = 'xtea';

        $encrypted = $mcryptSimpleIv->encrypt($data, $key, $algorithm);
        /** @noinspection SpellCheckingInspection */
        $this->assertEquals(
            'Bua24VBfkbZnZ+MiKHQu',
            base64_encode($encrypted)
        );

        $decrypted = $mcryptSimpleIv->decrypt($encrypted, $key, $algorithm);
        $this->assertEquals($data, $decrypted);
    }
}
