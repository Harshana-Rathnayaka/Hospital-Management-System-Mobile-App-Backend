<?php

require_once '../includes/dbOperations.php';

$response = array();

if (isset($_POST['appointment_id']) && isset($_POST['description'])) {

    $appointment_id = $_POST['appointment_id'];
    $description = trim($_POST['description']);

    // we can operate the data further
    $db = new DbOperations();

    $result = $db->updateDescription($appointment_id, $description);

    if ($result == 0) {

        // success
        $response['error'] = false;
        $response['message'] = "Appointment details were updated successfully!";

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
