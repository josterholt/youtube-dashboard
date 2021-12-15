<?php
namespace josterholt\Repository;
use josterholt\Service\GoogleService;
use josterholt\Service\RedisService;
use josterholt\Service\Fetch;

class PlayListItemRepository extends YouTubeRepository {
    protected static $_type = "video";

    public static function getByPlayListId(string $playlist_id): array {
        $fetch = new Fetch();
        self::$_useCache? $fetch->enableCache() : $fetch->disableCache();
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