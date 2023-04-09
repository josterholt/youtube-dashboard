<?php

namespace josterholt\Service;

use Psr\Log\LoggerInterface;
use Redislabs\Module\ReJSON\ReJSON;

/**
 * CacheFetch reads from Redis (with RedisJson module).
 * 
 * Example usage:
 * $fetch = new CacheFetch($redisInstance);
 * $results = $fetch->get("key", "json.path");
 */
class CacheFetch extends AbstractFetch
{
    protected $logger = null;
    private $_redis = null;
    protected $useReadCache = true;

    /**
     * Accepts a Redis client to use for caching as an argument.
     * 
     * @param LoggerInterface $logger Used for logging.
     * @param  ReJSON $redis Datastore utility.
     */
    public function __construct(LoggerInterface $logger, ReJSON $redis)
    {
        $this->logger = $logger;
        $this->_redis = $redis;
    }

    /**
     * Fetch will use cache before calling external resource.
     * 
     * @return void
     */
    public function enableReadCache()
    {
        $this->useReadCache = true;
    }

    /**
     * Fetch will retrieve fresh data from external resource,
     * even if cache exists. Results of get() will be cached.
     * 
     * @return void
     */
    public function disableReadCache()
    {
        $this->useReadCache = false;
    }

    /**
     * Returns query response data. Cached response is returned
     * if it exists and cache is enabled, otherwise a new call
     * is made against Google API.
     * 
     * @param  string   $key
     * @param  string   $path
     * @param  function $query
     * 
     * @return array array of responses
     */
    public function get(String $key, String $path, callable $query): array|null
    {
        $cache = $this->_redis->get($key, $path);
        if (!empty($cache)) {
            $this->logger->debug("<!-- Using cache for {$key} -->\n");
            return json_decode($cache);
        }
        return null;
    }
}
