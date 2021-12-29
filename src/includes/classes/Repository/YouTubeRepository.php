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
abstract class YouTubeRepository implements IGenericRepository
{
    protected $_type = null;
    protected $_service = null;
    protected $_useCache = true;
    protected $_readAdapter = null;
    protected $_logger = null;

    public function __construct(
        LoggerInterface $logger,
        GoogleAPIFetch $fetch,
        YouTube $youTubeAPI
    ) {
        $this->_logger = $logger;
        $this->_readAdapter = $fetch;
        $this->_service = $youTubeAPI;
    }    

    public function enableReadCache()
    {
        $this->_readAdapter->enableReadCache();
        $this->_useCache = true;
    }

    public function disableReadCache()
    {
        $this->_readAdapter->disableReadCache();
        $this->_useCache = false;
    }

    public function getReadCacheState()
    {
        return $this->_useCache;
    }

    public function getAll(): array
    {
        return [];
    }

    public function getById($id): object|null
    {
        return null;
    }
    
    public function create(object $record): bool
    {
        return false;
    }

    public function update(object $record): bool
    {
        return false;
    }

    public function delete($id): bool
    {
        return false;
    }
}
