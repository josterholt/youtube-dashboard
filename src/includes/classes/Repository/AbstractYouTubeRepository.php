<?php

namespace josterholt\Repository;

use josterholt\Service\YouTube;
use josterholt\Repository\IGenericRepository;
use Psr\Log\LoggerInterface;

/**
 * This repository type will retrieve YouTube videos, 
 * starting with CACHE and then querying source (YouTube).
 */
abstract class AbstractYouTubeRepository implements IGenericRepository
{
    protected $type = null;
    protected $service = null;
    protected $logger = null;

    public function __construct(
        LoggerInterface $logger,
        YouTube $youTubeAPI
    ) {
        $this->logger = $logger;
        $this->service = $youTubeAPI;
    }

    abstract public function getAll(): array;

    abstract public function getById($id): object|null;

    abstract public function create(object $record): bool;

    abstract public function update(object $record): bool;

    abstract public function delete($id): bool;
}
