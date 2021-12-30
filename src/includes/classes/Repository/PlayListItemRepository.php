<?php
namespace josterholt\Repository;

class PlayListItemRepository extends AbstractYouTubeRepository
{
    public function getByPlayListId(string $playlist_id): array
    {       
        try {
            $playlist_items = $this->readAdapter->get(
                "youtube.playlistItems.{$playlist_id}", '.', function ($queryParams) use ($playlist_id) {
                    $queryParams = [
                        'maxResults' => 25,
                        'playlistId' => $playlist_id
                    ];
                    $this->logger->debug("Fetching items for playlist ID: {$playlist_id}");
                    $items = $this->service->playlistItems->listPlaylistItems('snippet,contentDetails', $queryParams);
                    $this->logger->debug(count($items) . " playlist items found.");
                    return $items;
                }
            );
        } catch(\Exception $e) {
            $this->logger->error("Unable to access Playlist youtube.playlistItems.{$playlist_id}\n{$e->getMessage()}");
            return [];
        }

        return $playlist_items;
    }

    public function getAll(): array
    {
        return [];
    }

    public function getById($id): object|null
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
