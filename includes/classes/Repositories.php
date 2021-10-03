<?php
interface IGenericRepository { 
    public static function getAll(): array;
    public static function getById(int $id): object|null;
    public static function create(object $record): bool;
    public static function update(object $record): bool;
    public static function delete($id): bool;
}

/**
 * This repository type will retrieve YouTube videos, 
 * starting with CACHE and then querying source (YouTube).
 */
abstract class YouTubeRepository implements IGenericRepository  {
    protected static $_type = null;
    protected static $_service = null;
    protected static $_useCache = true;

    public static function setGoogleService(Google\Service $service) {
        self::$_service = $service;
    }

    public static function enableCache() {
        self::$_useCache = true;
    }

    public static function disableCache() {
        self::$_useCache = false;
    }

    public static function getAll(): array {
        return [];
    }

    public static function getById($id): object|null {
        return null;
    }
    
    public static function create(object $record): bool {
        return false;
    }

    public static function update(object $record): bool {
        return false;
    }

    public static function delete($id): bool {
        return false;
    }
}

class SubscriptionRepository extends YouTubeRepository {
    protected static $_type = 'subscription';


    public static function getAll(): array {
        $fetch = new Fetch();
        $fetch->setRedisClient(RedisService::getInstance());

        self::$_useCache? $fetch->enableCache() : $fetch->disableCache();
    
        // Fetch channel subscriptions of authenticated user.
        $results = $fetch->get('josterholt.youtube.subscriptions', '.', function ($queryParams) {
            $queryParams['mine'] = true;

            // @todo allow Google service to be assigned outside of this
            return GoogleService::getInstance()->subscriptions->listSubscriptions('contentDetails,snippet', $queryParams);
        });
    
        foreach ($results as $result) {
            foreach ($result->items as $item) {
                $subscriptions[] = $item;
            }
        }
        return $subscriptions;
    }    
}

class CategoryRepository extends YouTubeRepository {
    protected static $_type = "category";

    public static function getAll(): array {
        return RedisService::getInstance()->get("categories.names");
    }

    // This doesn't feel right. Maybe it needs to be broken out.
    public static function getItems(): array {
        return RedisService::getInstance()->getArray("categories.items"); // @todo Store categories in user specific namespace
    }
}

class ChannelRepository extends YouTubeRepository {
    protected static $_type = "channel";

    public static function getBySubscriptionId(string $subscription_id): array {
        $fetch = new Fetch();
        $fetch->setRedisClient(RedisService::getInstance()); // This shouldn't be hardcoded.
        
        try {
            // @todo is there a way to pull channels in bulk?
            $channels = $fetch->get("josterholt.youtube.channels.{$subscription_id}", '.', function ($queryParams) use($subscription_id)  {
                $queryParams = [
                    'id' => $subscription_id
                ];
                return GoogleService::getInstance()->channels->listChannels('snippet,contentDetails,statistics,contentOwnerDetails', $queryParams);
            });
        } catch(Exception $e) {
            echo $e->getMessage();
        }

        return $channels;
    }
}

class PlayListItemRepository extends YouTubeRepository {
    protected static $_type = "video";

    public static function getByPlayListId(string $playlist_id): array {
        $fetch = new Fetch();
        $fetch->setRedisClient(RedisService::getInstance()); // This shouldn't be hardcoded.
        
        try {
            $playlist_items = $fetch->get("josterholt.youtube.playlistItems.{$playlist_id}", '.', function ($queryParams) use ($playlist_id) {
                $queryParams = [
                    'maxResults' => 25,
                    'playlistId' => $playlist_id
                ];
    
                return GoogleService::getInstance()->playlistItems->listPlaylistItems('snippet,contentDetails', $queryParams);
            });
        } catch(Exception $e) {
            echo "josterholt.youtube.channels.{$subscription->snippet->resourceId->channelId}\n";        
            echo "Playlist josterholt.youtube.playlistItems.{$upload_playlist_id}\n";        
            echo $e->getMessage();
        }

        return $playlist_items;
    }
}