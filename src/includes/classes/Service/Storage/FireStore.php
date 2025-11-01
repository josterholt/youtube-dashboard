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
    protected ?LoggerInterface $logger = null;
    protected bool $useReadCache = true;
    protected ?FirestoreClient $_firestore = null;

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
    public function get(String $collection, String $key): array|null
    {
        $doc = $this->_firestore->collection($collection)->document($key);
        $snapshot_data = $doc->snapshot()->data();

        if(empty($snapshot_data)) {
            return null;
        }
        return [json_decode(json_encode($snapshot_data['serialized_value'][0]), FALSE)];
    }

    /**
     * Sets value in data store.
     * 
     * @param string $key
     * @param string $value
     */
    public function set(String $collection, String $key, String|array $value): void
    {
        $docRef = $this->_firestore->collection($collection)->document($key);
        $raw_document = [
            "serialized_value" => $value,
            "updated" => time(),
            "updated_readable" => date("Y-m-d H:i:s")
        ];
        $docRef->set($raw_document);
    }
}
