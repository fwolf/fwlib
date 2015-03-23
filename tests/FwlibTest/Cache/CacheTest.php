<?php
namespace FwlibTest\Cache;

use Fwlib\Cache\Cache;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CacheTest extends PHPUnitTestCase
{
    /**
     * @return Cache
     */
    protected function buildMock()
    {
        return Cache::create('');
    }


    /**
     * get(), set(), delete() etc
     */
    public function testCache()
    {
        $cache = $this->buildMock();

        $key = 'key';
        $val = 'val';
        $cache->set($key, $val);
        $this->assertEquals($val, $cache->get($key));
        $log = $cache->getLog();
        $log = array_pop($log);
        $this->assertTrue($log['success']);

        $cache->delete($key);
        $this->assertEquals(null, $cache->get($key));
        $log = $cache->getLog();
        $log = array_pop($log);
        $this->assertFalse($log['success']);


        // Val encode and decode
        $x = 'This is string.';
        $y = $this->reflectionCall($cache, 'encodeValue', [$x]);
        $y = $this->reflectionCall($cache, 'decodeValue', [$y]);
        $this->assertEquals($x, $y);

        // Encode/decode for array
        $cache->setConfig('storeMethod', 1);
        $x = ['a' => 'b'];
        $y = $this->reflectionCall($cache, 'encodeValue', [$x]);
        $y = $this->reflectionCall($cache, 'decodeValue', [$y]);
        $this->assertEquals($x, $y);


        // JSON decode to object
        // Decoded object s stdClass, not original __CLASS__, array property
        // in it need convert back from stdClass too.
        $cache->setConfig('storeMethod', 2);
        // Need config value, or json conversion will drop non-public property
        $x = ['foo' => 'bar'];
        $y = $this->reflectionCall($cache, 'encodeValue', [$x]);
        $y = $this->reflectionCall($cache, 'decodeValue', [$y]);
        $this->assertObjectHasAttribute('foo', $y);
        $this->assertInstanceOf('stdClass', $y);


        $this->assertEmpty($cache->getErrorMessage());
    }


    public function testEncodeValue()
    {
        $cache = $this->buildMock();

        // Encode/decode raw
        $cache->setConfig('storeMethod', 0);
        $x = 'test string';

        $y = $this->reflectionCall($cache, 'encodeValue', [$x]);
        $this->assertInternalType('string', $y);

        $y = $this->reflectionCall($cache, 'decodeValue', [$y]);
        $this->assertEquals($x, $y);
    }


    public function testExpire()
    {
        $cache = $this->buildMock();

        $this->assertFalse(
            $this->reflectionCall($cache, 'isExpired', ['any'])
        );


        $x = 0;
        $this->assertEquals(
            $x,
            $this->reflectionCall($cache, 'getExpireTime', [$x])
        );

        $x = time() + 2592000;
        $this->assertEquals(
            $x,
            $this->reflectionCall($cache, 'getExpireTime', [2592000])
        );

        $x = 2592001;
        $this->assertEquals(
            $x,
            $this->reflectionCall($cache, 'getExpireTime', [2592001])
        );

        $x = time() + 2592000;
        $cache->setConfig('lifetime', 2592000);
        $this->assertEquals(
            $x,
            $this->reflectionCall($cache, 'getExpireTime', [null])
        );
    }


    public function testVersion()
    {
        $cache = $this->buildMock();

        $key = 'test-ver';

        $this->assertEquals(1, $cache->getVersion($key));

        $cache->increaseVersion($key, 1);
        $this->assertEquals(2, $cache->getVersion($key));

        $cache->increaseVersion($key, 65534, 65535);
        $this->assertEquals(1, $cache->getVersion($key));
    }
}
