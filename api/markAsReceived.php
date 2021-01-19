<?php

session_start();
require_once '../includes/dbOperations.php';

$response = array();

if (isset($_POST['prescription_id'])) {

    $prescription_id = $_POST['prescription_id'];

    // we can operate the data further
    $db = new DbOperations();

    $result = $db->markAsReceived($prescription_id);

    if ($result == 0) {

        // success
        $response['error'] = false;
        $response['message'] = "Prescription marked as Received!";

    } elseif ($result == 1) {

        // some error
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again!";

    }
} else {

    // missing fields
    $response['error'] = true;
    $response['message'] = "Required fields are missing.";

}

// json output
echo json_encode($response);
