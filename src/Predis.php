<?php

namespace TraderInteractive\Memoize;

use Predis\ClientInterface;

/**
 * A memoizer that caches the results in a redis cache.
 */
class Predis implements Memoize
{
    /**
     * The predis client
     *
     * @var ClientInterface
     */
    private $client;

    /**
     * Cache refresh
     *
     * @var boolean
     */
    private $refresh;

    /**
     * Sets the predis client.
     *
     * @param ClientInterface $client  The predis client to use
     * @param boolean         $refresh If true we will always overwrite cache even if it is already set
     */
    public function __construct(ClientInterface $client, bool $refresh = false)
    {
        $this->client = $client;
        $this->refresh = $refresh;
    }

    /**
     * The value is stored in redis as a json_encoded string,
     * so make sure that the value you return from $compute is json-encode-able.
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
        if (!$this->refresh) {
            try {
                $cached = $this->client->get($key);
                if ($cached !== null) {
                    $data = json_decode($cached, true);
                    return $data['result'];
                }
            } catch (\Exception $e) {
                return call_user_func($compute);
            }
        }

        $result = call_user_func($compute);

        $this->cache($key, json_encode(['result' => $result]), $cacheTime);

        return $result;
    }

    /**
     * Caches the value into redis with errors suppressed.
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
            $this->client->set($key, $value);

            if ($cacheTime !== null) {
                $this->client->expire($key, $cacheTime);
            }
        } catch (\Exception $e) {
            // We don't want exceptions in accessing the cache to break functionality.
            // The cache should be as transparent as possible.
            // If insight is needed into these exceptions,
            // a better way would be by notifying an observer with the errors.
        }
    }
}
