<?php
namespace DominionEnterprises\Memoize;

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
    private $_cache = [];

    /**
     * $cacheTime is ignored - this will keep the results around for the lifetime of this instance.
     *
     * @see Memoize::memoizeCallable
     */
    public function memoizeCallable($key, $compute, $cacheTime = null)
    {
        if (array_key_exists($key, $this->_cache)) {
            return $this->_cache[$key];
        }

        $result = call_user_func($compute);
        $this->_cache[$key] = $result;

        return $result;
    }
}
