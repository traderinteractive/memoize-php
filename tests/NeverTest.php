<?php

namespace DominionEnterprises\Memoize;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \DominionEnterprises\Memoize\Never
 */
class NeverTest extends TestCase
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
        $compute = function() use(&$count, $value) {
            $count++;

            return $value;
        };

        $memoizer = new \DominionEnterprises\Memoize\Never();

        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame(2, $count);
    }
}
