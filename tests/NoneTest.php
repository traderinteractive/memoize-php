<?php

namespace TraderInteractiveTest\Memoize;

use PHPUnit\Framework\TestCase;
use TraderInteractive\Memoize\None;

/**
 * @coversDefaultClass \TraderInteractive\Memoize\None
 */
class NoneTest extends TestCase
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

        $memoizer = new None();

        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame(2, $count);
    }
}
