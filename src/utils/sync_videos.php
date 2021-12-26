<?php
use josterholt\Controller\SyncVideosController;

require_once __DIR__."/../includes/bootstrap.php";

$syncController = $container->make(SyncVideosController::class);
$syncController->sync();