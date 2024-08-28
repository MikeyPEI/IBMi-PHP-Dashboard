<?php

/**
 *
 * Functions related to the Db2 Services Application
 *
 *
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Db2 Connection via PDO

function getDb2Connection(): PDO
{
    // connect to IBM i
    try {
        $conn = new PDO("odbc:*LOCAL", "PHPUSER", "phpuser1");
    } catch (PDOException $exception) {
        echo "Connection failed. Please investigate";
        exit;
    }

    return $conn;
}

function getDb2PtfStatus(PDO $conn): array
{
    // Execute the service to collect Group PF status from Db2..


    $stmt = $conn->prepare("
        SELECT 
            PTF_GROUP_NAME, 
            PTF_GROUP_DESCRIPTION, 
            PTF_GROUP_LEVEL, 
            PTF_GROUP_TARGET_RELEASE
        FROM 
            QSYS2.GROUP_PTF_INFO
    ");

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDb2PtfCurrency(PDO $conn): array
{
    $stmt = $conn->prepare("SELECT * FROM systools.group_ptf_currency");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSystemValues(PDO $conn): array
{
    // Execute SQL statement to invoke service table with system values...

    $stmt = $conn->prepare("SELECT SYSTEM_VALUE_NAME, SYSTEM_VALUE FROM QSYS2.SYSTEM_VALUE_INFO 
                                       where SYSTEM_VALUE_NAME in ('QDATE', 'QSRLNBR', 'QIPLDATTIM', 'QMODEL', 'QPRCFEAT')");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getJobsByTemporaryStorage(PDO $conn): array
{

    // Execute SQL statement to invoke service table with jobs by temporary storage...


    $stmt = $conn->prepare("
        SELECT JOB_NAME_SHORT,
           JOB_TYPE,
           AUTHORIZATION_NAME,
           CPU_TIME,
           SUBSYSTEM,
           THREAD_COUNT,
           (TEMPORARY_STORAGE || ' MB') as TEMP_STORAGE
        FROM TABLE(QSYS2.ACTIVE_JOB_INFO()) X
        ORDER BY TEMPORARY_STORAGE DESC FETCH FIRST 100 ROWS ONLY
    ");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDiskAvailablePercent(PDO $conn): array
{

    // Invoke Db2 service that contains disk available percentage and capcity...

    $stmt = $conn->prepare("
        SELECT 
            CAST((FLOAT(TOTAL_CAPACITY_AVAILABLE) / FLOAT(TOTAL_CAPACITY)) as DECIMAL(17, 5)) as TOTAL_AVAIL_PERCENT, TOTAL_CAPACITY
        FROM QSYS2.ASP_INFO
    ");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getInquiryMessages(PDO $conn): array
{

    // Invoke Db2 Service to retrieve pending escape messages

    $stmt = $conn->prepare("
        SELECT a.message_timestamp, a.message_text, a.from_user, a.MESSAGE_TEXT, a.MESSAGE_SECOND_LEVEL_TEXT
        FROM qsys2.message_queue_info a exception JOIN  qsys2.message_queue_info b
        ON a.message_key = b.associated_message_key 
        WHERE a.message_type = 'INQUIRY' AND 
             (b.message_type in ('REPLY') or b.message_type is null) 
        ORDER BY b.message_timestamp DESC");
    
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function showInquiryMessage(?array $messages): void
{

    // Format the above messages in HTML table...
    if (empty($messages) ){
        echo '<h2 class="okay-message">No Inquiry Messages</h2>';

        return;
    }

    echo '<h2 class="error-message">Inquiry Messages found!  Go chase down your bad user!!</h2>';

    printTable($messages);
}

// function to accept associative array and print contents in HTML table with keys as heading and values as data in table cells
function printTable(array $data): void
{
    echo "<table>";
    echo "<tr>";

    foreach (array_keys($data[0]) as $key) {
        echo "<th>$key</th>";
    }

    echo "</tr>";

    foreach ($data as $row) {
        echo "<tr>";

        foreach ($row as $value) {
            echo "<td>$value</td>";
        }

        echo "</tr>";
    }

    echo "</table>";
}
