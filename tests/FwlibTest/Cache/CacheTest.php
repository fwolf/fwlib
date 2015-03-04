<?php
namespace FwlibTest\Cache;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Cache\Cache;

/**
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CacheTest extends PHPUnitTestCase
{
    /**
     * Cache instance
     *
     * @var Cache
     */
    protected $ch = null;

    public function __construct()
    {
        $this->ch = Cache::create('');
    }


    /**
     * get(), set(), delete() etc
     */
    public function testCache()
    {
        $key = 'key';
        $val = 'val';
        $this->ch->set($key, $val);
        $this->assertEquals($val, $this->ch->get($key));
        $log = $this->ch->getLog();
        $log = array_pop($log);
        $this->assertTrue($log['success']);

        $this->ch->delete($key);
        $this->assertEquals(null, $this->ch->get($key));
        $log = $this->ch->getLog();
        $log = array_pop($log);
        $this->assertFalse($log['success']);


        // Val encode and decode
        $x = 'This is string.';
        $y = $this->reflectionCall($this->ch, 'encodeValue', [$x]);
        $y = $this->reflectionCall($this->ch, 'decodeValue', [$y]);
        $this->assertEquals($x, $y);

        // Encode/decode for array
        $this->ch->setConfig('storeMethod', 1);
        $x = ['a' => 'b'];
        $y = $this->reflectionCall($this->ch, 'encodeValue', [$x]);
        $y = $this->reflectionCall($this->ch, 'decodeValue', [$y]);
        $this->assertEquals($x, $y);


        // JSON decode to object
        // Decoded object s stdClass, not original __CLASS__, array property
        // in it need convert back from stdClass too.
        $this->ch->setConfig('storeMethod', 2);
        // Need config value, or json conversion will drop non-public property
        $x = ['foo' => 'bar'];
        $y = $this->reflectionCall($this->ch, 'encodeValue', [$x]);
        $y = $this->reflectionCall($this->ch, 'decodeValue', [$y]);
        $this->assertObjectHasAttribute('foo', $y);
        $this->assertInstanceOf('stdClass', $y);


        $this->assertEmpty($this->ch->getErrorMessage());
    }


    public function testCreate()
    {
        $ch = Cache::create('');
        $this->assertInstanceOf('Fwlib\Cache\Cache', $ch);
    }


    public function testEncodeValue()
    {
        // Encode/decode raw
        $this->ch->setConfig('storeMethod', 0);
        $x = 'test string';

        $y = $this->reflectionCall($this->ch, 'encodeValue', [$x]);
        $this->assertInternalType('string', $y);

        $y = $this->reflectionCall($this->ch, 'decodeValue', [$y]);
        $this->assertEquals($x, $y);
    }


    public function testExpire()
    {
        $this->assertFalse(
            $this->reflectionCall($this->ch, 'isExpired', ['any'])
        );


        $x = 0;
        $this->assertEquals(
            $x,
            $this->reflectionCall($this->ch, 'getExpireTime', [$x])
        );

        $x = time() + 2592000;
        $this->assertEquals(
            $x,
            $this->reflectionCall($this->ch, 'getExpireTime', [2592000])
        );

        $x = 2592001;
        $this->assertEquals(
            $x,
            $this->reflectionCall($this->ch, 'getExpireTime', [2592001])
        );

        $x = time() + 2592000;
        $this->ch->setConfig('lifetime', 2592000);
        $this->assertEquals(
            $x,
            $this->reflectionCall($this->ch, 'getExpireTime', [null])
        );
    }


    public function testVersion()
    {
        $key = 'test-ver';

        $this->assertEquals(1, $this->ch->getVersion($key));

        $this->ch->increaseVersion($key, 1);
        $this->assertEquals(2, $this->ch->getVersion($key));

        $this->ch->increaseVersion($key, 65534, 65535);
        $this->assertEquals(1, $this->ch->getVersion($key));
    }
}
