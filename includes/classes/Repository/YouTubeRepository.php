<?php
namespace josterholt\Repository;
use josterholt\Repository\IGenericRepository;

/**
 * This repository type will retrieve YouTube videos, 
 * starting with CACHE and then querying source (YouTube).
 */
abstract class YouTubeRepository implements IGenericRepository  {
    protected $_type = null;
    protected $_service = null;
    protected $_useCache = true;

    public function setGoogleService(\Google\Service $service) {
        $this->_service = $service;
    }

    public function enableReadCache() {
        $this->_useCache = true;
    }

    public function disableReadCache() {
        $this->_useCache = false;
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