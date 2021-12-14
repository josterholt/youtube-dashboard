<?php
namespace josterholt\repository;

interface IGenericRepository { 
    public static function getAll(): array;
    public static function getById(int $id): object|null;
    public static function create(object $record): bool;
    public static function update(object $record): bool;
    public static function delete($id): bool;
}