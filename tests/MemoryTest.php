<?php

namespace TraderInteractiveTest\Memoize;

use PHPUnit\Framework\TestCase;
use TraderInteractive\Memoize\Memory;

/**
 * @coversDefaultClass \TraderInteractive\Memoize\Memory
 */
class MemoryTest extends TestCase
{
    /**
     * @test
     * @covers ::memoizeCallable
     */
    public function memoizeCallableTwice()
    {
        $count = 0;
        $key = 'foo';
        $value = 'bar';
        $compute = function () use (&$count, $value) {
            $count++;

            return $value;
        };

        $memoizer = new Memory();

        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame(1, $count);
    }
}
