<?php

/**
 * Iniital include for the dashboard of the application
 *
 *
 */


$cssStart = 'dashboard';

include __DIR__ . '/template/header.php';
include __DIR__ . '/template/menu.php';
?>

    <h1>PHP and Db2 Services for IBM i Programmers!!</h1>



<?php

require_once('ServiceFunctions.php');
// Connect to the database
$conn          = getDb2Connection();

//Print the system information
$systemValues = getSystemValues($conn);
printTable($systemValues);


// Get the disk utilization
$diskAvailable = getDiskAvailablePercent($conn);
$diskUsed      = round((1 - $diskAvailable['0']['TOTAL_AVAIL_PERCENT']) * 100,2);
$diskTotal = ($diskAvailable['0']['TOTAL_CAPACITY']/1000);
?>

    <div class="meter-container">
        <label for="disk">Disk Utilization: <?php echo $diskUsed . '% of '. $diskTotal .' GB'; ?></label>
        <meter id="disk" min="0" low="30" high="90" max="100" optimum="25" value="<?php echo $diskUsed; ?>"></meter>
    </div>


<?php

// get inquiry messages or not...

$messages = getInquiryMessages($conn);
showInquiryMessage($messages);