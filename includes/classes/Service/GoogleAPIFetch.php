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
class GoogleAPIFetch
{
    private $_redis = null;
    protected $_useReadCache = true;

    /**
     * Accepts a Redis client to use for caching as an argument.
     * 
     * @param \Redislabs\Module\ReJSON\ReJSON $redis
     * @return void
     */
    public function __construct(LoggerInterface $logger, ReJSON $redis)
    {
        $this->_logger = $logger;
        $this->_redis = $redis;
    }

    /**
     * Sets Redis client to use for caching.
     * 
     * @param Redislabs\Module\ReJSON\ReJSON $redis
     * @return void
     */
    protected function setRedisClient(ReJSON $redis)
    {
        $this->_redis = $redis;
    }

    /**
     * Fetch will use cache before calling external resource.
     */
    public function enableReadCache() {
        $this->_useReadCache = true;
    }

    /**
     * Fetch will retrieve fresh data from external resource,
     * even if cache exists. Results of get() will be cached.
     */
    public function disableReadCache() {
        $this->_useReadCache = false;
    }

    /**
     * Returns query response data. Cached response is returned
     * if it exists and cache is enabled, otherwise a new call
     * is made against Google API.
     * 
     * @param string $key
     * @param string $path
     * @param function $query
     * @param $forceRefresh
     * @return array of responses
     */
    public function get($key, $path, $query)
    {
        if ($this->_useReadCache) {
            $cache = $this->_redis->get($key, $path);
            if (!empty($cache)) {
                echo "<!-- Using cache for {$key} -->\n";
                return json_decode($cache);
            }
        }


        $loop = true;
        $queryParams = ['maxResults' => 500];
        $responses = [];
        while ($loop) {
            $response = $query($queryParams);
            if (empty($response)) {
                $loop = false;
                $this->_logger->debug("Empty response.", $queryParams);
                continue;
            } else {
                $responses[] = $response->toSimpleObject(); // \Google\Collection
            }


            // Setup next page
            if (empty($response->getNextPageToken()) || (isset($queryParams['pageToken']) && $response->getNextPageToken() == $queryParams['pageToken'])) {
                $loop = false;
            } else {
                $queryParams['pageToken'] = $response->getNextPageToken();
                $this->_logger->debug("Page token set to {$queryParams['pageToken']}");
            }
        }

        $responsesJSONEncoded = json_encode($responses);
        $this->_logger->debug("|Setting cache record.", ["key" => $key, "path" => $path, "length" => strlen($responsesJSONEncoded)]);
        $this->_redis->set($key, $path, $responsesJSONEncoded); // Support array of requests

        return $responses;
    }
}
