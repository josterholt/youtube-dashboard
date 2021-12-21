<?php
use josterholt\Service\GoogleAPIFetch;
use DI\Container;
use Redislabs\Module\ReJSON\ReJSON;
use josterholt\Service\GoogleService;
use Monolog\Handler\StreamHandler;
use MonoLog\Logger;
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

require_once("functions.php");


$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->useAnnotations(true);
$container = $containerBuilder->build();

$logContainerBuilder = \DI\create(Monolog\Logger::class);
$logContainerBuilder->constructor("frontend-webapp");
$logContainerBuilder->method('pushHandler', new StreamHandler('php://stdout', Logger::DEBUG));
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

register_shutdown_function(function () use ($redisClient) {
    $redisClient->close();
});

$redisJSONClient = ReJSON::createWithPhpRedis($redisClient);
$container->set(ReJSON::class, $redisJSONClient);
// REDIS END

// GOOGLE SERVICE START
//GoogleService::initialize();
$googleService = $container->get(GoogleService::class);
$googleService->initialize();
// GOOGLE SERVICE END


// FETCH START
$fetchObjectBuilder = \DI\create(GoogleAPIFetch::class);
$fetchObjectBuilder->constructor(\DI\get(Psr\Log\LoggerInterface::class), \DI\get(ReJSON::class));
$fetchObjectBuilder->method('enableReadCache', \DI\get(GoogleAPIFetch::class));
$container->set(GoogleAPIFetch::class, $fetchObjectBuilder);
// FETCH END