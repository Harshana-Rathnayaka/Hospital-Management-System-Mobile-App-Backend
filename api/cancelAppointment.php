<?php

require_once '../includes/dbOperations.php';

$response = array();

if (isset($_POST['appointment_id'])) {

    $appointment_id = $_POST['appointment_id'];

    // we can operate the data further
    $db = new DbOperations();

    $result = $db->cancelAppointment($appointment_id);

    if ($result == 0) {

        // success
        $response['error'] = false;
        $response['message'] = "Appointment cancelled!";

    } elseif ($result == 1) {

        // some error
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again later!";

    }
} else {

    // missing fields
    $response['error'] = true;
    $response['message'] = "Required fields are missing";

}

// json output
echo json_encode($response);
