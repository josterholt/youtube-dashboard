<?php

namespace josterholt\Service;

use josterholt\Service\Storage\AbstractStore;
use Psr\Log\LoggerInterface;

class YouTube extends \Google\Service\YouTube
{
    /**
     * Cache store for read 
     */
    private AbstractStore | null $store;

    /**
     * Caching system flag for read calls
     */
    protected bool $useCache = true;

    /**
     * Logger
     */
    protected LoggerInterface $logger;

    /**
     * Constructs the internal representation of the YouTube service.
     *
     * @param Client|array $clientOrConfig The client used to deliver requests, or a
     *                                     config array to pass to a new Client instance.
     * @param string $rootUrl The root URL used for requests to the service.
     */
    public function __construct(LoggerInterface $logger, $clientOrConfig = [], $rootUrl = null, AbstractStore $store = null)
    {
        parent::__construct($clientOrConfig, $rootUrl);

        $this->logger = $logger;
        $this->store = $store;
    }

    /**
     * Enables caching system for read calls
     */
    public function enableReadCache()
    {
        $this->useCache = true;
    }

    /**
     * Disables caching system for read calls
     */
    public function disableReadCache()
    {
        $this->useCache = false;
    }

    /**
     * Gets current status of caching system for read calls
     */
    public function getReadCacheState()
    {
        return $this->useCache;
    }
    /**
     * Retrieves requests from cache if applicable
     * 
     * @param string $key Key for cache
     * @param callable $callback Callback to get requests
     */
    public function queryFromCache(String $key, callable $callback)
    {
        if ($this->useCache && !empty($this->store)) {
            $cache = $this->store->get($key);
            if (!empty($cache)) {
                $this->logger->debug("<!-- Using cache for {$key} -->\n");
                return $cache;
            }
        }

        $responses = $this->getAllGoogleServiceResponses($callback);

        if (!empty($this->store)) {
            $responsesJSONEncoded = json_encode($responses);
            $this->logger->debug("Setting cache record.", ["key" => $key, "path" => ".", "length" => strlen($responsesJSONEncoded)]);
            $this->store->set($key, $responsesJSONEncoded); // Support array of requests
        }

        return $responses;
    }

    /**
     * Returns query response data. Cached response is returned
     * if it exists and cache is enabled, otherwise a new call
     * is made against Google API.
     * 
     * @param  function $query
     * 
     * @return array array of responses
     */
    public function getAllGoogleServiceResponses($query): array|null
    {
        $loop = true;
        $queryParams = ['maxResults' => 500];
        $responses = [];
        while ($loop) {
            $response = $query($queryParams);
            if (empty($response)) {
                $loop = false;
                // $this->logger->debug("Empty response.", $queryParams);
                continue;
            } else {
                // $this->logger->debug("Adding response to array.");
                $responses[] = $response->toSimpleObject(); // \Google\Collection
            }

            // Setup next page
            if (empty($response->getNextPageToken()) || (isset($queryParams['pageToken']) && $response->getNextPageToken() == $queryParams['pageToken'])) {
                // $this->logger->debug("Ending loop.");
                $loop = false;
            } else {
                $queryParams['pageToken'] = $response->getNextPageToken();
                // $this->logger->debug("Page token set to {$queryParams['pageToken']}");
            }
        }

        return $responses;
    }
}
