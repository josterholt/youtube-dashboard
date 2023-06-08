<?php

namespace josterholt\Service\Storage;

use Psr\Log\LoggerInterface;
use Google\Cloud\Firestore\FirestoreClient;

/**
 * FireFetch reads from Redis (with RedisJson module).
 * 
 * Example usage:
 * $fetch = new FireFetch();
 * $results = $fetch->get("key", "json.path");
 */
class FireStore extends AbstractStore
{
    protected $logger = null;
    protected $useReadCache = true;
    protected $_firestore = null;

    /**
     * Accepts a Redis client to use for caching as an argument.
     * 
     * @param LoggerInterface $logger Used for logging.
     */
    public function __construct(LoggerInterface $logger, FirestoreClient $firestore)
    {
        $this->logger = $logger;
        $this->_firestore = $firestore;
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
    public function get(String $key): array|null
    {
        return [$this->_firestore->collection("cache")->document($key)->serialized_value()];
    }

    /**
     * Sets value in data store.
     * 
     * @param string $key
     * @param string $value
     */
    public function set(String $key, String $value): void
    {
        $docRef = $this->_firestore->collection("cache")->document($key);
        $docRef->set(["serialized_value" => $value]);
    }
}
