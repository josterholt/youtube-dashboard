<?php
namespace josterholt\Repository;

use josterholt\Service\GoogleService;

class PlayListItemRepository extends YouTubeRepository
{
    protected $_type = "video";

    public function getByPlayListId(string $playlist_id): array
    {       
        try {
            $playlist_items = $this->_readAdapter->get(
                "youtube.playlistItems.{$playlist_id}", '.', function ($queryParams) use ($playlist_id) {
                    $queryParams = [
                    'maxResults' => 25,
                    'playlistId' => $playlist_id
                    ];
                    $this->_logger->debug("Fetching items for playlist ID: {$playlist_id}");
                    $items = $this->_service->playlistItems->listPlaylistItems('snippet,contentDetails', $queryParams);
                    $this->_logger->debug(count($items) . " playlist items found.");
                    return $items;
                }
            );
        } catch(\Exception $e) {
            $this->_logger->error("Unable to access Playlist youtube.playlistItems.{$playlist_id}\n{$e->getMessage()}");
            return [];
        }

        return $playlist_items;
    }
}
