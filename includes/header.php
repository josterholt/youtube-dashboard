<?php

/**
 * BEGIN AUTOLOAD SCRIPTS
 */
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    throw new Exception(sprintf('Please run "composer require google/apiclient:~2.0" in "%s"', __DIR__));
}
require_once __DIR__ . '/../vendor/autoload.php';
/**
 * END AUTOLOAD SCRIPTS
 */

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once("functions.php");

GoogleService::initialize();
RedisService::initialize();

// @todo there needs to be a more graceful way to handle no service.
if(GoogleService::getInstance() == null) {
    die("Unable to connect to Google Service\n");
}
