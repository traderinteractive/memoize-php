<?php
namespace DominionEnterprises\Memoize;

use \Predis\ClientInterface;

/**
 * A memoizer that caches the results in a redis cache.  
 */
class Predis implements Memoize
{
    /**
     * The predis client
     *
     * @var \Predis\ClientInterface
     */
    private $_client;

    /**
     * Sets the predis client.
     *
     * @param \Predis\ClientInterface $client The predis client to use
     */
    public function __construct(ClientInterface $client)
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

        try {
            $this->_client->set($key, json_encode(array('result' => $result)));

            if ($cacheTime !== null) {
                $this->_client->expire($key, $cacheTime);
            }
        } catch (\Exception $e) {
            return $result;
        }

        return $result;
    }
}
