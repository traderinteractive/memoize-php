<?php
namespace DominionEnterprises\Memoize;

/**
 * @coversDefaultClass \DominionEnterprises\Memoize\Null
 */
class NullTest extends \PHPUnit_Framework_TestCase
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

        $memoizer = new \DominionEnterprises\Memoize\Null();

        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame($value, $memoizer->memoizeCallable($key, $compute));
        $this->assertSame(2, $count);
    }
}
