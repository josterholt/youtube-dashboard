<?php
namespace josterholt\Repository;
class PlayListItemRepository extends YouTubeRepository {
    protected $_type = "video";

    public function getByPlayListId(string $playlist_id): array {
        $this->_useCache? $this->_readAdapter->enableReadCache() : $this->_readAdapter->disableReadCache();
        
        try {
            $playlist_items = $this->_readAdapter->get("youtube.playlistItems.{$playlist_id}", '.', function ($queryParams) use ($playlist_id) {
                $queryParams = [
                    'maxResults' => 25,
                    'playlistId' => $playlist_id
                ];

                return $this->_service::getInstance()->playlistItems->listPlaylistItems('snippet,contentDetails', $queryParams);
            });
        } catch(\Exception $e) {
            $this->_logger->error("Unable to access Playlist youtube.playlistItems.{$playlist_id}\n{$e->getMessage()}");
            return [];
        }

        return $playlist_items;
    }
}