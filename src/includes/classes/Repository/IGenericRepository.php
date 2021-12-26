<?php
namespace josterholt\Repository;

interface IGenericRepository
{
 
    public function getAll(): array;
    public function getById(int $id): object|null;
    public function create(object $record): bool;
    public function update(object $record): bool;
    public function delete($id): bool;
}
