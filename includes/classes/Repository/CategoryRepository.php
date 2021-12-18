<?php
namespace josterholt\Repository;
use josterholt\Service\RedisService;

// TODO: This needs to only implement IGenericRepository and not extend.
class CategoryRepository extends YouTubeRepository {
    protected $_type = "category";

    // TODO: Add unit test for CategoryRepository::getAll()
    public function getAll(): array {
        return RedisService::getInstance()->get("categories.names");
    }

    // TODO: Add unit test for CategoryRepository::getItems()
    // This doesn't feel right. Maybe it needs to be broken out.
    public function getItems(): array {
        return RedisService::getInstance()->getArray("categories.items"); // @todo Store categories in user specific namespace
    }
}