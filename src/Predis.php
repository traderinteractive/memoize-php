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
     * The percentage of requests that check for refreshes
     *
     * @var int
     */
    private $refreshPercent;

    /**
     * The multiplier to use on runtime when deciding to do a refresh
     *
     * @var float
     */
    private $runtimeMultiplier;

    /**
     * Sets the predis client.
     *
     * @param ClientInterface $client         The predis client to use
     * @param boolean         $refresh        If true we will always overwrite cache even if it is already set
     * @param int             $refreshPercent The percentage of requests that check for refreshes
     */
    public function __construct(
        ClientInterface $client,
        bool $refresh = false,
        int $refreshPercent = 0,
        float $runtimeMultiplier = 3
    ) {
        $this->client = $client;
        $this->refresh = $refresh;
        $this->refreshPercent = $refreshPercent;
        $this->runtimeMultiplier = $runtimeMultiplier;
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
                if (rand(1, 100) <= $this->refreshPercent) {
                    // {$refreshPercent}% of requests should check to see if this key is almost expired.
                    // We don't want to check this on every call to preserve performance.
                    // Also, this functionality is only important for requests that have many concurrent calls.
                    $runtime = $this->client->get("{$key}.runtime");
                    $ttl = $this->client->pttl($key) / 1000;
                    if ($runtime && $runtime * $this->runtimeMultiplier > $ttl) {
                        return $this->getData($key, $compute, $cacheTime);
                    }
                }

                $cached = $this->client->get($key);
                if ($cached !== null) {
                    $data = json_decode($cached, true);
                    return $data['result'];
                }
            } catch (\Exception $e) {
                return $this->getData($key, $compute, $cacheTime);
            }
        }

        return $this->getData($key, $compute, $cacheTime);
    }

    private function getData(string $key, callable $compute, int $cacheTime = null)
    {
        $start = microtime(true);
        $result = call_user_func($compute);
        $runtime = microtime(true) - $start;

        $this->cache($key, json_encode(['result' => $result]), $cacheTime, $runtime);

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
    private function cache(string $key, string $value, int $cacheTime = null, float $runtime)
    {
        try {
            $this->client->set($key, $value);
            $this->client->set("{$key}.runtime", $runtime);

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
