<?php

namespace josterholt\Service\Storage;

use Psr\Log\LoggerInterface;
use Redislabs\Module\ReJSON\ReJSON;

/**
 * GoogleAPIFetch is a wrapper for Google API that caches results.
 * 
 * GoogleAPIFetch uses Redis (with RedisJson module) to cache results of the query (function)
 * passed into the get() method. Results are cached using the key and path passed into the get() method.
 * 
 * Example usage:
 * $fetch = new StoreImpl();
 * $results = $fetch->get("key", "json.path", function () {
 *     return file_get_contents("https://placeholder.com/api/endpoint)
 * });
 */
abstract class AbstractStore
{
    protected $logger = null;

    /**
     * Accepts a Redis client to use for caching as an argument.
     * 
     * @param LoggerInterface $logger Used for logging.
     * @param  ReJSON $redis Datastore utility.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns query response data. Cached response is returned
     * if it exists and cache is enabled, otherwise a new call
     * is made against Google API.
     * 
     * @param  string   $key
     * 
     * @return array array of responses
     */
    abstract public function get(String $key): mixed;

    /**
     * Sets value in data store.
     * 
     * @param string $key
     * @param string $value
     */
    abstract public function set(String $key, String $value): void;
}
