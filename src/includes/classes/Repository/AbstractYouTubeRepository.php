<?php
namespace josterholt\Repository;

use Google\Service\YouTube;
use josterholt\Repository\IGenericRepository;
use josterholt\Service\GoogleAPIFetch;
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
    protected $readAdapter = null;
    protected $logger = null;

    public function __construct(
        LoggerInterface $logger,
        GoogleAPIFetch $fetch,
        YouTube $youTubeAPI
    ) {
        $this->logger = $logger;
        $this->readAdapter = $fetch;
        $this->service = $youTubeAPI;
    }    

    public function enableReadCache()
    {
        $this->readAdapter->enableReadCache();
        $this->useCache = true;
    }

    public function disableReadCache()
    {
        $this->readAdapter->disableReadCache();
        $this->useCache = false;
    }

    public function getReadCacheState()
    {
        return $this->useCache;
    }

    abstract public function getAll(): array;

    abstract public function getById($id): object|null;
    
    abstract public function create(object $record): bool;

    abstract public function update(object $record): bool;

    abstract public function delete($id): bool;
}
