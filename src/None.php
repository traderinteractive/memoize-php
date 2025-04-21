<?php

namespace TraderInteractive\Memoize;

/**
 * A memoizer that never caches and always recomputes the result.
 * This is useful for turning off memoization (e.g., for debugging).
 */
class None implements Memoize
{
    /**
     * $cacheTime and $key are ignored - this always calls $compute.
     *
     * @see Memoize::memoizeCallable
     *
     * @param string   $key
     * @param callable $compute
     * @param int|null $cacheTime
     * @param bool     $refresh
     *
     * @return mixed
     */
    public function memoizeCallable(string $key, callable $compute, int $cacheTime = null, bool $refresh = false)
    {
        return call_user_func($compute);
    }
}
