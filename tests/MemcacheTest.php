<?php

namespace TraderInteractive\Memoize;

use PHPUnit\Framework\TestCase;
use TraderInteractiveTest\Memoize\MemcacheMockable;

/**
 * @coversDefaultClass \TraderInteractive\Memoize\Memcache
 * @covers ::<private>
 */
class MemcacheTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct
     * @covers ::memoizeCallable
     */
    public function memoizeCallableWithCachedValue()
    {
        $count = 0;
        $key = 'foo';
        $value = 'bar';
        $cachedValue = json_encode(['result' => $value]);
        $compute = function () use (&$count, $value) {
            $count++;

            return $value;
        };


        $client = $this->getMemcacheMock();
        $client->expects(
            $this->once()
        )->method('get')->with($this->equalTo($key))->will($this->returnValue($cachedValue));

        $memoizer = new Memcache($client);

        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame(0, $count);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::memoizeCallable
     */
    public function memoizeCallableWithExceptionOnGet()
    {
        $count = 0;
        $key = 'foo';
        $value = 'bar';
        $compute = function () use (&$count, $value) {
            $count++;

            return $value;
        };

        $client = $this->getMemcacheMock();
        $client->expects(
            $this->once()
        )->method('get')->with($this->equalTo($key))->will($this->throwException(new \Exception()));

        $memoizer = new Memcache($client);

        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame(1, $count);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::memoizeCallable
     */
    public function memoizeCallableWithUncachedKey()
    {
        $count = 0;
        $key = 'foo';
        $value = 'bar';
        $cachedValue = json_encode(['result' => $value]);
        $cacheTime = 1234;
        $compute = function () use (&$count, $value) {
            $count++;

            return $value;
        };

        $client = $this->getMemcacheMock();
        $client->expects($this->once())->method('get')->with($this->equalTo($key))->will($this->returnValue(null));
        $client->expects($this->once())->method('set')
            ->with($this->equalTo($key), $this->equalTo($cachedValue), $this->equalTo(0), $this->equalTo($cacheTime));

        $memoizer = new Memcache($client);

        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute, $cacheTime));
        $this->assertSame(1, $count);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::memoizeCallable
     */
    public function memoizeCallableWithUncachedKeyWithExceptionOnSet()
    {
        $count = 0;
        $key = 'foo';
        $value = 'bar';
        $cachedValue = json_encode(['result' => $value]);
        $compute = function () use (&$count, $value) {
            $count++;

            return $value;
        };

        $client = $this->getMemcacheMock();
        $client->expects(
            $this->once()
        )->method('get')->with($this->equalTo($key))->will($this->returnValue(null));
        $setExpectation = $client->expects(
            $this->once()
        )->method('set')->with($this->equalTo($key), $this->equalTo($cachedValue));
        $setExpectation->will($this->throwException(new \Exception()));

        $memoizer = new Memcache($client);

        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame(1, $count);
    }

    public function getMemcacheMock() : \Memcache
    {
        $isOlderPHPVersion = PHP_VERSION_ID < 80000;
        $memcacheClass = $isOlderPHPVersion ? MemcacheMockable::class : \Memcache::class;
        return $this->getMockBuilder($memcacheClass)->setMethods(['get', 'set'])->getMock();
    }
}
