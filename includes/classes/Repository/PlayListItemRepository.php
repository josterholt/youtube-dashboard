<?php
namespace josterholt\Repository;
use josterholt\Service\GoogleService;
use josterholt\Service\RedisService;
use josterholt\Service\GoogleAPIFetch;

class PlayListItemRepository extends YouTubeRepository {
    protected $_type = "video";

    public function getByPlayListId(string $playlist_id): array {
        $fetch = new GoogleAPIFetch(RedisService::getInstance());
        $this->_useCache? $fetch->enableReadCache() : $fetch->disableReadCache();
        $fetch->setRedisClient(RedisService::getInstance()); // This shouldn't be hardcoded.
        
        try {
            $playlist_items = $fetch->get("josterholt.youtube.playlistItems.{$playlist_id}", '.', function ($queryParams) use ($playlist_id) {
                $queryParams = [
                    'maxResults' => 25,
                    'playlistId' => $playlist_id
                ];

                return GoogleService::getInstance()->playlistItems->listPlaylistItems('snippet,contentDetails', $queryParams);
            });
        } catch(\Exception $e) {
            echo "Playlist josterholt.youtube.playlistItems.{$playlist_id}\n";        
            echo $e->getMessage();
            return [];
        }

        return $playlist_items;
    }
}