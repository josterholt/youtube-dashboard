<?php
namespace josterholt\Service;

class Fetch
{
    private $_redis = null;
    protected $_useCache = true;

    /**
     * @param Redislabs\Module\ReJSON\ReJSON $redis
     * @return void
     */
    public function setRedisClient(\Redislabs\Module\ReJSON\ReJSON $redis)
    {
        $this->_redis = $redis;
    }

    /**
     * Fetch will use cache before calling external resource.
     */
    public function enableCache() {
        $this->_useCache = true;
    }

    /**
     * Fetch will retrieve fresh data from external resource,
     * even if cache exists.
     */
    public function disableCache() {
        $this->_useCache = false;
    }

    /**
     * @param string $key
     * @param string $path
     * @param string $query
     * @param $forceRefresh
     * @return array of responses
     */
    public function get($key, $path, $query)
    {
        if ($this->_useCache) {
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
                continue;
            } else {
                $responses[] = $response->toSimpleObject();
            }


            // Setup next page
            if (empty($response->getNextPageToken()) || (isset($queryParams['pageToken']) && $response->getNextPageToken() == $queryParams['pageToken'])) {
                $loop = false;
            } else {
                $queryParams['pageToken'] = $response->getNextPageToken();
            }
        }
        $this->_redis->set($key, $path, json_encode($responses)); // Support array of requests

        return $responses;
    }
}
