<?php

use josterholt\Service\Storage\AbstractStore;
use josterholt\Service\GoogleService;
use josterholt\Repository\PlayListItemRepository;
use josterholt\Repository\ChannelRepository;
use josterholt\Repository\SubscriptionRepository;
use Redislabs\Module\ReJSON\ReJSON;
use Psr\Log\LoggerInterface;
use Monolog\Handler\StreamHandler;
use MonoLog\Logger;
use Google\Client;
use josterholt\Service\YouTube;
use josterholt\Service\Storage\RedisStore;

/**
 * BEGIN AUTOLOAD SCRIPTS
 */
if (!file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    throw new Exception(sprintf('Please run "composer require google/apiclient:~2.0" in "%s"', __DIR__));
}
require_once __DIR__ . '/../../vendor/autoload.php';
/**
 * END AUTOLOAD SCRIPTS
 */

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->useAnnotations(true);
$container = $containerBuilder->build();

$logContainerBuilder = \DI\create(Monolog\Logger::class);
$logContainerBuilder->constructor("frontend-webapp");
$logContainerBuilder->method('pushHandler', new StreamHandler('php://stdout', Logger::ERROR));

$container->set(Monolog\Logger::class, $logContainerBuilder);
$container->set("Psr\Log\LoggerInterface", $logContainerBuilder);

// REDIS START
$redisClient = $container->get(\Redis::class);
$redisURL = $_ENV['REDIS_URL'];
$redisPort = $_ENV['REDIS_PORT'];
$redisPassword = $_ENV['REDIS_PASSWORD'];
$redisClient->connect($redisURL, $redisPort);
if ($redisPassword != null) {
    $redisClient->auth($redisPassword);
}

register_shutdown_function(
    function () use ($redisClient) {
        $redisClient->close();
    }
);

$redisJSONClient = ReJSON::createWithPhpRedis($redisClient);
$container->set(ReJSON::class, $redisJSONClient);
// REDIS END


// FIREBASE START
// putenv("GOOGLE_APPLICATION_CREDENTIALS=" . __DIR__ . '/../../secrets/youtube-dashboard-325222-firebase-adminsdk-77t7j-b446e44ddf.json');
// $firestore = new FirestoreClient();
// $container->set(FirebaseService::class, $firestore);
// FIREBASE END


// GOOGLE CLIENT START
$googleClientBuilder = \DI\create(Client::class);
$container->set(Client::class, $googleClientBuilder);
// GOOGLE CLIENT END

// GOOGLE SERVICE START
$googleServiceBuilder = \DI\Create(GoogleService::class);
$googleServiceBuilder->constructor(
    \DI\get(Client::class),
    $_ENV['CLIENT_SECRET_FILE_PATH'],
    $_ENV['ACCESS_TOKEN_FILE_PATH'],
    \DI\get(LoggerInterface::class)
);
$container->set(GoogleService::class, $googleServiceBuilder);

$googleClientCode = empty($_GET['code']) ? null : $_GET['code'];
$googleService = $container->get(GoogleService::class);
$googleService->initialize($googleClientCode);
// GOOGLE SERVICE END

// FETCH START
$fetchObjectBuilder = \DI\create(RedisStore::class);
$fetchObjectBuilder->constructor(\DI\get(LoggerInterface::class), \DI\get(ReJSON::class));
// $fetchObjectBuilder->method('enableReadCache');
$container->set(AbstractStore::class, $fetchObjectBuilder);
// FETCH END

// YouTube API START
$youTubeBuilder = \DI\create(YouTube::class)
    ->constructor(\DI\get(LoggerInterface::class), $googleService->getClient(), null, \DI\get(AbstractStore::class));
$container->set(YouTube::class, $youTubeBuilder);
// YouTube API END


// REPO INIT START
$categoryRepositoryBuilder = \DI\create(CategoryRepository::class);
$categoryRepositoryBuilder->constructor(
    \DI\get(LoggerInterface::class),
    \DI\get(ReJSON::class)
);
$container->set(CategoryRepository::class, $categoryRepositoryBuilder);


$playlistItemRepositoryBuilder = \DI\create(PlayListItemRepository::class);
$playlistItemRepositoryBuilder->constructor(
    \DI\get(LoggerInterface::class),
    \DI\get(YouTube::class)
);
$container->set(PlayListItemRepository::class, $playlistItemRepositoryBuilder);


$channelRepositoryBuilder = \DI\create(ChannelRepository::class);
$channelRepositoryBuilder->constructor(
    \DI\get(LoggerInterface::class),
    \DI\get(YouTube::class)
);
$container->set(ChannelRepository::class, $channelRepositoryBuilder);

$subscriptionRepositoryBuilder = \DI\create(SubscriptionRepository::class);
$subscriptionRepositoryBuilder->constructor(\DI\get(LoggerInterface::class), \DI\get(YouTube::class));
$container->set(SubscriptionRepository::class, $subscriptionRepositoryBuilder);
// REPO INIT END
