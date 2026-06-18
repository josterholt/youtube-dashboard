#!/usr/local/bin/php
<?php

use josterholt\Controller\SyncVideosController;

define("BASE_APPLICATION_DIR", "");

require_once __DIR__ . "/../includes/bootstrap.php";

if (!$googleService->isAuthenticated) {
    if (file_exists("secrets/access_token.json")) {
        unlink("secrets/access_token.json");
    }

    if (php_sapi_name() == "cli") {
        echo "\n\n";
        if (!$googleService->authenticateViaLoopback()) {
            exit(1);
        }
    }
}

$channel_id = null;
if ($argc > 1) {
    $channel_id = $argv[1];
}

$syncController = $container->make(SyncVideosController::class);
$syncController->sync($channel_id);
