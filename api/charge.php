<?php

require_once '../includes/dbOperations.php';
$response = array();

// db object
$db = new DbOperations();

// sanitizing the POST array
$POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);

// first get the payment type
$payment_for = $POST['payment_for'];

// now check what kind of payment it is (APPOINTMENT/ LAB_TEST)
if ($payment_for == 'APPOINTMENT') {

    // payment type is APPOINTMENT

    // getting the values
    $patient_id = $POST['patient_id'];
    $appointment_id = $POST['appointment_id'];
    $amount = $POST['amount'];
    $stripe_customer_id = $POST['stripe_customer_id'];

    $real_amount = substr($amount, 0, 4);

    // adding to the payments table in the db
    $result = $db->addToPayments($patient_id, $payment_for, $real_amount, $stripe_customer_id);

    if ($result == 0) {

        $result2 = $db->markAsPaid($appointment_id);

        if ($result2 == 0) {

            // pament was successful and added to both stripe and the db
            $response['error'] = false;
            $response['message'] = 'Payment for the ' . $payment_for . ' was successful. Payment ID - ' . $stripe_customer_id;

        } elseif ($result2 == 1) {

            // could not add to the db
            $response['error'] = true;
            $response['message'] = "Something went wrong, " . $payment_for . " could not be marked as PAID!";
        }

    } elseif ($result == 1) {

        // payment was not successful and could not be added to the stripe dashboard
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again!";

    }
} elseif ($payment_for == 'LAB_TEST') {

    // payment type is LAB_TEST

    // getting the values
    $patient_id = $POST['patient_id'];
    $amount = $POST['amount'];
    $details = $POST['details'];
    $stripe_customer_id = $POST['stripe_customer_id'];

    $real_amount = substr($amount, 0, 4);

    // adding to the payments table in the db
    $result = $db->addToPayments($patient_id, $payment_for, $real_amount, $stripe_customer_id);

    if ($result == 0) {

        // creating the lab test request in the lab_tests table
        $result2 = $db->requestALabTest($patient_id, $details);

        if ($result2 == 0) {

            // payment was successful and added the lab test request to the db
            $response['error'] = false;
            $response['message'] = "Lab Test request submitted successfully! Payment ID - " . $stripe_customer_id;

        } elseif ($result2 == 1) {

            // lab test request was not added to the db
            $response['error'] = true;
            $response['message'] = "Something went wrong, lab test request could not be submitted. Please try again later!";

        }

    } elseif ($result == 1) {

        // payment was not successful and could not be added to the stripe dashboard
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again later!";

    }

}

echo json_encode($response);
