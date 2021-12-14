<?php
namespace josterholt\repository;
use josterholt\repository\IGenericRepository;

/**
 * This repository type will retrieve YouTube videos, 
 * starting with CACHE and then querying source (YouTube).
 */
abstract class YouTubeRepository implements IGenericRepository  {
    protected static $_type = null;
    protected static $_service = null;
    protected static $_useCache = true;

    public static function setGoogleService(\Google\Service $service) {
        self::$_service = $service;
    }

    public static function enableCache() {
        self::$_useCache = true;
    }

    public static function disableCache() {
        self::$_useCache = false;
    }

    public static function getAll(): array {
        return [];
    }

    public static function getById($id): object|null {
        return null;
    }
    
    public static function create(object $record): bool {
        return false;
    }

    public static function update(object $record): bool {
        return false;
    }

    public static function delete($id): bool {
        return false;
    }
}