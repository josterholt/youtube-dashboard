<?php

use josterholt\Controller\SyncVideosController;

require_once __DIR__ . "/../includes/bootstrap.php";

if (!$googleService->isAuthenticated) {
    unlink("secrets/access_token.json");

    echo "\n\n";
    echo "\033[32mUse the following URL to authenticate:\033[39m\n";
    echo "\033[34m" . $googleService->getAuthorizationPageURL() . "\033[39m\n";
    exit(0);
}

$channel_id = null;
if ($argc > 1) {
    $channel_id = $argv[1];
}

$syncController = $container->make(SyncVideosController::class);
$syncController->sync($channel_id);
