<?php
namespace josterholt\Repository;
use Redislabs\Module\ReJSON\ReJSON;

// TODO: This needs to only implement IGenericRepository and not extend.
class CategoryRepository implements IGenericRepository
{
    protected $_type = "category";
    private $_redis = null;

    /**
     * @param ReJSON $redis
     */
    public function __construct(ReJSON $redis)
    {
        $this->_redis = $redis;
    }

    // TODO: Add unit test for CategoryRepository::getAll()
    public function getAll(): array
    {
        return $this->_redis->get("categories.names");
    }

    // TODO: Add unit test for CategoryRepository::getItems()
    // This doesn't feel right. Maybe it needs to be broken out.
    public function getItems(): array
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
