<?php
namespace josterholt\Repository;
use Redislabs\Module\ReJSON\ReJSON;

/**
 * Data query interface for category items. Items contain category ID and an item ID.
 */
class CategoryItemRepository implements IGenericRepository
{
    private $_redis = null;

    /**
     * @param ReJSON $redis
     */
    public function __construct(ReJSON $redis)
    {
        $this->_redis = $redis;
    }

    public function getAll(): array
    {
        return $this->_redis->getArray("categories.items");
    }

    public function getById(int $id): object|null 
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
