<?php

session_start();
require_once '../includes/dbOperations.php';

$response = array();

if (isset($_POST['test_id'])) {

    $lab_test_id = $_POST['test_id'];

    // we can operate the data further
    $db = new DbOperations();

    $result = $db->cancelLabTest($lab_test_id);

    if ($result == 0) {

        // success
        $response['error'] = false;
        $response['message'] = "The Lab Test was cancelled!";

    } elseif ($result == 1) {

        // some error
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again later!";

    }
} else {

    // missing fields
    $response['error'] = true;
    $response['message'] = "Required fields are missing!";

}

// json output
echo json_encode($response);
