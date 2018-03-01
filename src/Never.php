<?php

namespace TraderInteractive\Memoize;

/**
 * A memoizer that never caches and always recomputes the result.
 * This is useful for turning off memoization (e.g., for debugging).
 */
class Never implements Memoize
{
    /**
     * $cacheTime and $key are ignored - this always calls $compute.
     *
     * @see Memoize::memoizeCallable
     */
    public function memoizeCallable($key, $compute, $cacheTime = null)
    {
        return call_user_func($compute);
    }
}
