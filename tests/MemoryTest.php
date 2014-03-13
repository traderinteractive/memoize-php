<?php
namespace DominionEnterprises\Memoize;

/**
 * @coversDefaultClass \DominionEnterprises\Memoize\Memory
 */
class MemoryTest extends \PHPUnit_Framework_TestCase
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

        $memoizer = new Memory();

        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame(1, $count);
    }
}
