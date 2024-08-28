<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

/**
 * PHP Script to lst the PTF Group Status for the partition where run...
 *
 * Connect to Db2, then run the function associated with the task...
 */

require_once __DIR__ . '/ServiceFunctions.php';

$cssStart = 'active-jobs';

include __DIR__ . '/template/header.php';
include __DIR__ . '/template/menu.php';

echo '<h2>Active Jobs By Temp Storage</h2>';

$connection_to_Db2 = getDb2Connection();

$groupStatus = getJobsByTemporaryStorage($connection_to_Db2);

printTable($groupStatus);

include __DIR__ . '/template/footer.php';
