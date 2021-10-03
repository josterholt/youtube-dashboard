<?php
define("FETCH_REFRESH_CACHE", true);
define("FETCH_USE_CACHE", false);


class Fetch
{
    private $_redis = null;

    /**
     * @param string $url
     * @param int $port
     * @param string $password
     */
    public function setupRedisCache(?string $url = "localhost", ?int $port = 6379, ?string $password = null)
    {
        $this->_redis = getReJSONClient($url, $port, $password);
    }

    /**
     * @param Redislabs\Module\ReJSON\ReJSON $redis
     * @return void
     */
    public function setRedisClient(Redislabs\Module\ReJSON\ReJSON $redis)
    {
        $this->_redis = $redis;
    }

    /**
     * (set REFRESH/USE CACHE)
     */

    /**
     * @param string $key
     * @param string $path
     * @param string $query
     * @param $forceRefresh
     * @return array of responses
     */
    public function get($key, $path, $query, $forceRefresh = false)
    {
        if (!$forceRefresh) {
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
