<?php

namespace TraderInteractive\Memoize;

/**
 * An in-memory memoizer that keeps the cached values around for the lifetime of this instance.
 */
class Memory implements Memoize
{
    /**
     * The saved results
     *
     * @var array
     */
    private $cache = [];

    /**
     * $cacheTime is ignored - this will keep the results around for the lifetime of this instance.
     *
     * @see Memoize::memoizeCallable
     *
     * @param string   $key
     * @param callable $compute
     * @param int|null $cacheTime
     *
     * @return mixed
     */
    public function memoizeCallable(string $key, callable $compute, int $cacheTime = null)
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $result = call_user_func($compute);
        $this->cache[$key] = $result;

        return $result;
    }
}
