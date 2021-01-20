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

        // doctors table
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
    } elseif ($list_type == 'prescriptions') {

        // prescription table
        $prescriptions = $db->getIncomingPrescriptionsByUser($user_id);
        $prescriptionList = array();
        if ($prescriptions->num_rows > 0) {
            while ($row = $prescriptions->fetch_assoc()) {
                $prescriptionList[] = $row;
                $response['prescriptionList'] = $prescriptionList;
            }
        } else {
            $response['prescriptionList'] = [];
        }
    } elseif ($list_type == 'payable') {

        // payable appointments
        $payable = $db->getPayableAppointmentsByUser($user_id);
        $payableList = array();
        if ($payable->num_rows > 0) {
            while ($row = $payable->fetch_assoc()) {
                $payableList[] = $row;
                $response['payableList'] = $payableList;
            }
        } else {
            $response['payableList'] = [];
        }
    } elseif ($list_type == 'ongoing_lab_tests') {
        $lab_tests = $db->getOngoingLabTestsByUser($user_id);
        $labTestList = array();
        if ($lab_tests->num_rows > 0) {
            while ($row = $lab_tests->fetch_assoc()) {
                $labTestList[] = $row;
                $response['labTestList'] = $labTestList;
            }
        } else {
            $response['labTestList'] = [];
        }
    } 

} else {
    $response['error'] = true;
    $response['message'] = "Some fields are missing!";
}

echo json_encode($response);
