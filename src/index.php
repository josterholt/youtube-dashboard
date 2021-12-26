<?php

use josterholt\Controller\YouTubeDashboardController;


/**
 * Bootstrap includes container and logger initialization.
 * TODO: See if initialization can be moved into this file 
 * with a one liner for each initialization.
 */
require_once "includes/bootstrap.php";

$controller = $container->make(YouTubeDashboardController::class);
$controller->videoListing();
unset($controller);
