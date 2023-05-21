<?php

use josterholt\Controller\CategoryAPIController;

require_once __DIR__ . "/../../includes/bootstrap.cache_only.php";

$controller = $container->make(CategoryAPIController::class);
$controller->addItemToCategory();
unset($controller);
