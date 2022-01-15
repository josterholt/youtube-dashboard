<?php
use josterholt\Controller\CategoryAPIController;

require_once __DIR__."/../../includes/bootstrap.php";

if (!$googleService->isAuthenticated) {
    echo "\n\n";
    echo "Use the following URL to authenticate:\n";
    echo $googleService->getAuthorizationPageURL()."\n";
    exit(0);
}

$controller = $container->make(CategoryAPIController::class);
$controller->addItemToCategory();
unset($controller);