<?php
namespace DominionEnterprises\Memoize;

use \Predis\Client;

/**
 * A memoizer that caches the results in a redis cache.  
 */
class Predis implements Memoize
{
    /**
     * The predis client
     *
     * @var \Predis\Client
     */
    private $_client;

    /**
     * Sets the predis client.
     *
     * @param \Predis\Client $client The predis client to use
     */
    public function __construct(Client $client)
    {
        $this->_client = $client;
    }

    /**
     * The value is stored in redis as a json_encoded string, so make sure that the value you return from $compute is json-encodable.
     *
     * @see Memoize::memoizeCallable
     */
    public function memoizeCallable($key, $compute, $cacheTime = null)
    {
        try {
            $cached = $this->_client->get($key);
            if ($cached !== null) {
                $data = json_decode($cached, true);
                return $data['result'];
            }
        } catch (\Exception $e) {
            return call_user_func($compute);
        }

        $result = call_user_func($compute);

        $this->_cache($key, json_encode(array('result' => $result)), $cacheTime);

        return $result;
    }

    /**
     * Caches the value into redis with errors suppressed.
     *
     * @param string $key The key.
     * @param string $value The value.
     * @param int $cacheTime The optional cache time
     * @return void
     */
    private function _cache($key, $value, $cacheTime = null)
    {
        try {
            $this->_client->set($key, $value);

            if ($cacheTime !== null) {
                $this->_client->expire($key, $cacheTime);
            }
        } catch (\Exception $e) {
        }
    }
}
