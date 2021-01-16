<?php

require_once '../includes/dbOperations.php';
$response = array();

if (isset($_POST['user_id']) && isset($_POST['list_type'])) {

    $user_id = $_POST['user_id'];
    $list_type = $_POST['list_type'];

    // db object
    $db = new DbOperations();

    if ($list_type == 'appointments') {

        // appointments table
        $appointments = $db->getAppointments($user_id);
        $appointmentList = array();
        if ($appointments->num_rows > 0) {
            while ($row = $appointments->fetch_assoc()) {
                $appointmentList[] = $row;
                $response['appointmentList'] = $appointmentList;
            }
        } else {
            $response['appointmentList'] = [];
        }
    } elseif ($list_type == 'doctors') {
        $doctors = $db->getDoctors();
        $doctorsList = array();
        if ($doctors->num_rows > 0) {
            while ($row = $doctors->fetch_assoc()) {
                $doctorsList[] = $row;
                $response['doctorsList'] = $doctorsList;
            }
        } else {
            $response['doctorsList'] = [];
        }
    }

} else {
    $response['error'] = true;
    $response['message'] = "Some fields are missing!";
}

echo json_encode($response);
