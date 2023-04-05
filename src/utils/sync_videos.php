<?php

use josterholt\Controller\SyncVideosController;

require_once __DIR__ . "/../includes/bootstrap.php";

if (!$googleService->isAuthenticated) {
    echo "\n\n";
    echo "\033[32mUse the following URL to authenticate:\033[39m\n";
    echo "\033[34m" . $googleService->getAuthorizationPageURL() . "\033[39m\n";
    exit(0);
}

$syncController = $container->make(SyncVideosController::class);
$syncController->sync();
