<?php
namespace josterholt\Service;

class RedisService {
    protected static $_instance = null;

    public static function initialize() {
        self::getInstance();
    }

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = self::getReJSONClient($_ENV['REDIS_URL'], $_ENV['REDIS_PORT'], $_ENV['REDIS_PASSWORD']);
        }
        
        return self::$_instance;
    }

    /**
     * Returns a ReJSON client. Connection will auto-close at end of script.
     * @param string $url
     * @param string $port
     * @param string $password
     * @return Redislabs\Module\ReJSON\ReJSON
     */
    protected static function getReJSONClient(string $url, int $port, string $password = null): \Redislabs\Module\ReJSON\ReJSON
    {
        $redisClient = new \Redis();
        $redisClient->connect($url, $port);
        if ($password != null) {
            $redisClient->auth($password);
        }

        register_shutdown_function(function () use ($redisClient) {
            $redisClient->close();
        });
        
        return \Redislabs\Module\ReJSON\ReJSON::createWithPhpRedis($redisClient);
    }    
}