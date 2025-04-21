<?php

namespace TraderInteractive\Memoize;

use PHPUnit\Framework\TestCase;

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
