<?php

namespace josterholt\Repository;

use Redislabs\Module\ReJSON\ReJSON;


class CategoryNameRepository implements IGenericRepository
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
        return $this->_redis->get("categories.names");
    }

    public function getById(int $id): array|object|null
    {
        $names = $this->_redis->get("categories.names");
        $key = array_search($id, array_column($names, 'id'));

        if ($key === false) {
            return null;
        }

        return $names[$key];
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
