<?php

namespace TraderInteractive\Memoize;

/**
 * A memoizer that caches the results in memcache.
 */
class Memcache implements Memoize
{
    /**
     * The memcache client
     *
     * @var \Memcache
     */
    private $client;

    /**
     * Cache refresh
     *
     * @var boolean
     */
    private $refresh;

    /**
     * Sets the memcache client.
     *
     * @param \Memcache $client  The memcache client to use
     * @param boolean         $refresh If true we will always overwrite cache even if it is already set
     */
    public function __construct(\Memcache $client, bool $refresh = false)
    {
        $this->client = $client;
        $this->refresh = $refresh;
    }

    /**
     * The value is stored in memcache as a json_encoded string,
     * so make sure that the value you return from $compute is json-encode-able.
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
        if (!$this->refresh && !$refresh) {
            try {
                $cached = $this->client->get($key, $flags, $flags);
                if ($cached !== false && $cached != null) {
                    $data = json_decode($cached, true);
                    return $data['result'];
                }
            } catch (\Exception $e) {
                return call_user_func($compute);
            }
        }

        $result = call_user_func($compute);

        // If the result is false/null/empty, then there is no point in storing it in cache.
        if ($result === false || $result == null || empty($result)) {
            return $result;
        }

        $this->cache($key, json_encode(['result' => $result]), $cacheTime);

        return $result;
    }

    /**
     * Caches the value into memcache with errors suppressed.
     *
     * @param string $key       The key.
     * @param string $value     The value.
     * @param int    $cacheTime The optional cache time
     *
     * @return void
     */
    private function cache(string $key, string $value, int $cacheTime = null)
    {
        try {
            $this->client->set($key, $value, 0, $cacheTime);
        } catch (\Exception $e) {
            // We don't want exceptions in accessing the cache to break functionality.
            // The cache should be as transparent as possible.
            // If insight is needed into these exceptions,
            // a better way would be by notifying an observer with the errors.
        }
    }
}
