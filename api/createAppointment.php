<?php

require_once '../includes/dbOperations.php';

$response = array();

if (
    isset($_POST['user_id']) && isset($_POST['doctor_id']) && isset($_POST['description'])

) {

    $user_id = $_POST['user_id'];
    $doctor_id = $_POST['doctor_id'];
    $description = trim($_POST['description']);

    // we can operate the data further
    $db = new DbOperations();

    $result = $db->createAppointment($user_id, $doctor_id, $description);

    if ($result == 0) {

        // success
        $response['error'] = false;
        $response['message'] = "Appointment request submitted successfully!";

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
