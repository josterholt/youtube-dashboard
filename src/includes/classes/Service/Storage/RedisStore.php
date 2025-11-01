<?php

namespace josterholt\Service\Storage;

use Psr\Log\LoggerInterface;
use Redislabs\Module\ReJSON\ReJSON;

/**
 * RedisFetch reads from Redis (with RedisJson module).
 * 
 * Example usage:
 * $fetch = new RedisFetch($redisInstance);
 * $results = $fetch->get("key", "json.path");
 */
class RedisStore extends AbstractStore
{
    protected ?LoggerInterface $logger = null;
    private $_redis = null;

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
     * Returns query response data. Cached response is returned
     * if it exists and cache is enabled, otherwise a new call
     * is made against Google API.
     * 
     * @param  string   $key
     * 
     * @return array array of responses
     */
    public function get(String $collection, String $key): array|null
    {
        $cache_key = "{$collection}.{$key}";
        $cache = $this->_redis->get($cache_key, ".");
        if (!empty($cache)) {
            $this->logger->debug("<!-- Using cache for {$cache_key} -->\n");
            $return_val = json_decode($cache, false);
            return $return_val;
        }
        return null;
    }

    public function set(String $collection, String $key, array|String $value): void
    {
        $this->_redis->set("{$collection}.{$key}", ".", $value);
    }
}
