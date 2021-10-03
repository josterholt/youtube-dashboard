<?php
interface IGenericRepository { 
    public function getAll(): array;
    public function getById(int $id): object;
    public function create(object $record): bool;
    public function update(object $record): bool;
    public function delete($id): bool;
}

/**
 * This repository type will retrieve YouTube videos, 
 * starting with CACHE and then querying source (YouTube).
 */
abstract class YouTubeRepository implements IGenericRepository  {
    protected $_type = null;

    public function getAll(): array {

    }

    public function getById($id): object {

    }
    
    public function create(object $record): bool {

    }

    public function update(object $record): bool {

    }

    public function delete($id): bool {

    }
}

class CategoryRepository extends YouTubeRepository {
    protected $_type = "category";
}

class ChannelRepository implements YouTubeRepository {
    protected $_type = "channel";
}

class VideoRepository implements YouTubeRepository {
    protected $_type = "video";
}
