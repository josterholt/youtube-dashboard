<?php

namespace josterholt\Service;

use Psr\Log\LoggerInterface;
use Redislabs\Module\ReJSON\ReJSON;

/**
 * GoogleAPIFetch is a wrapper for Google API that caches results.
 * 
 * GoogleAPIFetch uses Redis (with RedisJson module) to cache results of the query (function)
 * passed into the get() method. Results are cached using the key and path passed into the get() method.
 * 
 * Example usage:
 * $fetch = new GoogleAPIFetch($redisInstance);
 * $results = $fetch->get("key", "json.path", function () {
 *     return file_get_contents("https://placeholder.com/api/endpoint)
 * });
 */
abstract class AbstractFetch
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
    abstract public function get(String $key, String $path, callable $query): array|null;
}
