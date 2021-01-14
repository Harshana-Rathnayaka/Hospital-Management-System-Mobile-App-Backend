<?php

require_once '../includes/dbOperations.php';

$response = array();

if (
    isset($_POST['full_name']) &&
    isset($_POST['username']) &&
    isset($_POST['email']) &&
    isset($_POST['contact']) &&
    isset($_POST['address']) &&
    isset($_POST['password'])
) {

    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $password = $_POST['password'];

    // we can operate the data further
    $db = new DbOperations();

    $result = $db->registerUser($full_name, $username, $email, $contact, $address, $password);

    if ($result == 1) {

        // success
        $response['error'] = false;
        $response['message'] = "You have registered successfully, Please log in!";

    } elseif ($result == 2) {

        // some error
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again!";

    } elseif ($result == 0) {

        // user exists
        $response['error'] = true;
        $response['message'] = "It seems that this user already exists, please choose a different email and username.";

    }
} else {

    // missing fields
    $response['error'] = true;
    $response['message'] = "Required fields are missing";

}

// json output
echo json_encode($response);
