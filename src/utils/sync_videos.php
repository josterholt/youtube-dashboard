<?php
use josterholt\Controller\SyncVideosController;

require_once __DIR__."/../includes/bootstrap.php";

if (!$googleService->isAuthenticated) {
    echo "\n\n";
    echo "Use the following URL to authenticate:\n";
    echo $googleService->getAuthorizationPageURL()."\n";
    exit(0);
}

$syncController = $container->make(SyncVideosController::class);
$syncController->sync();
