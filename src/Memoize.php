<?php

namespace TraderInteractive\Memoize;

interface Memoize
{
    /**
     * Gets the value stored in the cache or uses the passed function to compute the value and save to cache.
     *
     * @param string   $key          The key to fetch
     * @param callable $compute      A function to run if the value was not cached that will return the result.
     * @param int      $cacheTime    The number of seconds to cache the response for, or null to not expire it ever.
     * @param bool     $shouldUpdate Sets whether the cache should update itself during the call.
     *
     * @return mixed The data requested, optionally pulled from cache
     */
    public function memoizeCallable(string $key, callable $compute, int $cacheTime = null, bool $shouldUpdate = false);
}
