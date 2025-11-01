#!/usr/local/bin/php
<?php

use josterholt\Controller\SyncVideosController;

define("BASE_APPLICATION_DIR", "");

require_once __DIR__ . "/../includes/bootstrap.php";

if (!$googleService->isAuthenticated) {
    if (file_exists("secrets/access_token.json")) {
        unlink("secrets/access_token.json");
    }

    // if ($_ENV['GOOGLE_SERVICE_AUTHENTICATION_TYPE'] == "CLIENT") {
    if (php_sapi_name() == "cli") {
        echo "\n\n";
        echo "\033[32mUse the following URL to authenticate:\033[39m\n";
        echo "\033[34m" . $googleService->getAuthorizationPageURL() . "\033[39m\n";
        exit(0);
    }
}

$channel_id = null;
if ($argc > 1) {
    $channel_id = $argv[1];
}

$syncController = $container->make(SyncVideosController::class);
$syncController->sync($channel_id);
