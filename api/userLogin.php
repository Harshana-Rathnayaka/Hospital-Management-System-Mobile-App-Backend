<?php

require_once '../includes/dbOperations.php';

$response = array();

if (isset($_POST['username']) and isset($_POST['password'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    // db object
    $db = new DbOperations();

    if ($db->userLogin($username, $password)) {

        // getting user data
        $user = $db->getUserByUsername($username);

        // checks if the user is approved
        if ($user['user_status'] == 'ACTIVE') {

            // patient account
            if ($user['user_type'] == 'PATIENT') {

                // session and reroute
                $response['error'] = false;
                $response['username'] = $user['username'];
                $response['full_name'] = $user['full_name'];
                $response['email'] = $user['email'];
                $response['user_id'] = $user['user_id'];
                $response['user_type'] = $user['user_type'];

            } else {

                // incorrect account type
                $response['error'] = true;
                $response['message'] = "Please use the web application to log in.";

            }

        } else {

            // account not active
            $response['error'] = true;
            $response['message'] = "Your account is not active. Please create a new account or try again later.";
        }

    } else {

        // incorrect username or password
        $response['error'] = true;
        $response['message'] = "The username or password you entered is incorrect. Please check again.";

    }
} else {

    // missing fields
    $response['error'] = true;
    $response['message'] = "Required fields are missing";

}

// json output
echo json_encode($response);
