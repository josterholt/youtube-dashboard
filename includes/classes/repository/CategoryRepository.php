<?php
namespace josterholt\repository;
use josterholt\service\RedisService;

class CategoryRepository extends YouTubeRepository {
    protected static $_type = "category";

    public static function getAll(): array {
        return RedisService::getInstance()->get("categories.names");
    }

    // This doesn't feel right. Maybe it needs to be broken out.
    public static function getItems(): array {
        return RedisService::getInstance()->getArray("categories.items"); // @todo Store categories in user specific namespace
    }
}