<?php

namespace josterholt\Repository;

use Google\Service\YouTube;
use josterholt\Repository\IGenericRepository;
use josterholt\Service\Storage\AbstractStore;
use Psr\Log\LoggerInterface;

/**
 * This repository type will retrieve YouTube videos, 
 * starting with CACHE and then querying source (YouTube).
 */
abstract class AbstractYouTubeRepository implements IGenericRepository
{
    protected $type = null;
    protected $service = null;
    protected $useCache = true;
    protected ?AbstractStore $store = null;
    protected $logger = null;

    public function __construct(
        LoggerInterface $logger,
        AbstractStore $store,
        YouTube $youTubeAPI
    ) {
        $this->logger = $logger;
        $this->store = $store;
        $this->service = $youTubeAPI;
    }

    public function enableReadCache()
    {
        $this->useCache = true;
    }

    public function disableReadCache()
    {
        $this->useCache = false;
    }

    public function getReadCacheState()
    {
        return $this->useCache;
    }

    public function _getValueFromStore(String $key, callable $callback)
    {
        if ($this->useCache) {
            $cache = $this->store->get($key);
            if (!empty($cache)) {
                $this->logger->debug("<!-- Using cache for {$key} -->\n");
                return $cache;
            }
        }

        $responses = $this->getAllGoogleServiceResponses($callback);

        $responsesJSONEncoded = json_encode($responses);
        $this->logger->debug("Setting cache record.", ["key" => $key, "path" => ".", "length" => strlen($responsesJSONEncoded)]);
        $this->store->set($key, $responsesJSONEncoded); // Support array of requests

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
                $this->logger->debug("Empty response.", $queryParams);
                continue;
            } else {
                $this->logger->debug("Adding response to array.");
                $responses[] = $response->toSimpleObject(); // \Google\Collection
            }

            // Setup next page
            if (empty($response->getNextPageToken()) || (isset($queryParams['pageToken']) && $response->getNextPageToken() == $queryParams['pageToken'])) {
                $this->logger->debug("Ending loop.");
                $loop = false;
            } else {
                $queryParams['pageToken'] = $response->getNextPageToken();
                $this->logger->debug("Page token set to {$queryParams['pageToken']}");
            }
        }

        return $responses;
    }

    abstract public function getAll(): array;

    abstract public function getById($id): object|null;

    abstract public function create(object $record): bool;

    abstract public function update(object $record): bool;

    abstract public function delete($id): bool;
}
