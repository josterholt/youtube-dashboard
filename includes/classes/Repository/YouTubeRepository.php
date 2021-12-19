<?php
namespace josterholt\Repository;

use Google\Service\YouTube;
use josterholt\Repository\IGenericRepository;
use josterholt\Service\GoogleAPIFetch;
use josterholt\Service\GoogleService;

/**
 * This repository type will retrieve YouTube videos, 
 * starting with CACHE and then querying source (YouTube).
 */
abstract class YouTubeRepository implements IGenericRepository  {
    protected $_type = null;
    protected $_service = null;
    protected $_useCache = true;
    protected $_readAdapter = null;

    public function __construct(GoogleAPIFetch $fetch, GoogleService $googleService) {
        $this->_readAdapter = $fetch;
        $this->_service = $googleService;
        
    }    

    public function setGoogleService(\Google\Service $service) {
        $this->_service = $service;
    }

    public function enableReadCache() {
        $this->_useCache = true;
    }

    public function disableReadCache() {
        $this->_useCache = false;
    }

    public function getReadCacheState() {
        return $this->_useCache;
    }

    public function getAll(): array {
        return [];
    }

    public function getById($id): object|null {
        return null;
    }
    
    public function create(object $record): bool {
        return false;
    }

    public function update(object $record): bool {
        return false;
    }

    public function delete($id): bool {
        return false;
    }
}