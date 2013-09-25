<?php
namespace FwlibTest\Cache;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Cache\Cache;

/**
 * Test for Fwlib\Cache\Cache
 *
 * @package     FwlibTest\Cache
 * @copyright   Copyright 2012-2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2012-11-06
 */
class CacheTest extends PHPunitTestCase
{
    /**
     * Cache object
     *
     * @var Fwlib\Cache\Cache
     */
    protected $ch = null;

    public function __construct()
    {
        $this->ch = Cache::create('');
    }


    /**
     * get(), set(), del() etc
     */
    public function testCache()
    {
        $key = 'key';
        $val = 'val';
        $this->ch->set($key, $val);
        $this->assertEquals($val, $this->ch->get($key));

        $this->ch->del($key);
        $this->assertEquals(null, $this->ch->get($key));


        // Val encode and decode
        $x = 'This is string.';
        $this->assertEquals(
            $x,
            $this->ch->decodeVal($this->ch->encodeVal($x))
        );

        // Encode/decode for array
        $this->ch->setConfig('cache-store-method', 1);
        $x = array('a' => 'b');
        $this->assertEquals(
            $x,
            $this->ch->decodeVal($this->ch->encodeVal($x))
        );


        // JSON decode to object
        // Decoded object s stdClass, not original __CLASS__, array property
        // in it need convert back from stdClass too.
        $this->ch->setConfig('cache-store-method', 2);
        $x = new Cache;
        $y = $this->ch->decodeVal($this->ch->encodeVal($x));
        $this->assertObjectHasAttribute('config', $y);
        $this->assertObjectHasAttribute('config', $y->config);
        $this->assertInstanceOf('stdClass', $y->config->config);
        // Convert stdClass back to array
        $this->assertEqualArray($x->config->config, (array)$y->config->config);
    }


    public function testCreate()
    {
        $ch = Cache::create('');
        $this->assertInstanceOf('Fwlib\Cache\Cache', $ch);
    }


    public function testEncodeVal()
    {
        // Encode/decode raw
        $this->ch->setConfig('cache-store-method', 0);
        $x = 'test string';
        $y = $this->ch->encodeVal($x);
        $this->assertInternalType('string', $y);
        $this->assertEquals($x, $this->ch->decodeVal($y));
    }


    public function testExpire()
    {
        $this->assertFalse($this->ch->expire('any'));


        $x = 0;
        $this->assertEquals($x, $this->ch->expireTime($x));

        $x = time() + 2592000;
        $this->assertEquals($x, $this->ch->expireTime(2592000));

        $x = 2592001;
        $this->assertEquals($x, $this->ch->expireTime(2592001));

        $x = time() + 2592000;
        $this->assertEquals($x, $this->ch->expireTime());
    }


    public function testVer()
    {
        $key = 'test-ver';

        $this->assertEquals(1, $this->ch->ver($key));

        $this->ch->ver($key, 1);
        $this->assertEquals(2, $this->ch->ver($key));

        $this->ch->ver($key, 65534, 65535);
        $this->assertEquals(1, $this->ch->ver($key));
    }
}
