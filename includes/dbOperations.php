<?php

class DbOperations
{

    private $con;

    public function __construct()
    {

        require_once dirname(__FILE__) . '/dbConnection.php';

        $db = new DbConnect();

        $this->con = $db->connect();
    }

    /* CRUD  -> C -> CREATE */

    // user registration
    public function registerUser($full_name, $username, $email, $contact, $address, $pass)
    {
        $password = md5($pass); // password encrypting
        if ($this->isUserExist($username, $email)) {
            // user exists
            return 0;
        } else {
            $stmt = $this->con->prepare("INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `contact`, `address`, `password`, `user_type`, `user_status`) VALUES (NULL, ?, ?, ?, ?, ?, ?, 'PATIENT', 'ACTIVE');");
            $stmt->bind_param("ssssss", $full_name, $username, $email, $contact, $address, $password);

            if ($stmt->execute()) {
                // user registered
                return 1;
            } else {
                // some error
                return 2;
            }
        }
    }

    // user login
    public function userLogin($username, $pass)
    {
        $password = md5($pass); // password decrypting
        $stmt = $this->con->prepare("SELECT `user_id` FROM `users` WHERE `username` = ? AND `password` = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // create new appointment by user
    public function createAppointment($user_id, $doctor_id, $description)
    {
        $stmt = $this->con->prepare("INSERT INTO `appointments` (`appointment_id`, `patient_id`, `doctor_id`, `description`, `appointment_status`) VALUES (NULL, ?, ?, ?, 'PENDING');");
        $stmt->bind_param("iis", $user_id, $doctor_id, $description);

        if ($stmt->execute()) {
            // new appointment created
            return 0;
        } else {
            // some error
            return 1;
        }
    }

    // make payments by user
    public function addToPayments($patient_id, $payment_for, $amount, $stripe_customer_id)
    {
        $stmt = $this->con->prepare("INSERT INTO `payments`(`payment_id`, `patient_id`, `payment_for`, `paid_amount`, `stripe_customer_id`) VALUES (NULL, ?, ?, ?, ?);");
        $stmt->bind_param("isss", $patient_id, $payment_for, $amount, $stripe_customer_id);

        if ($stmt->execute()) {
            // payment created
            return 0;
        } else {
            // some error
            return 1;
        }
    }

    // marking the appointment as PAID by the user
    public function markAsPaid($appointment_id)
    {

        $stmt = $this->con->prepare("UPDATE `appointments` SET `appointment_status` = 'PAID' WHERE `appointment_id` = ?");
        $stmt->bind_param("i", $appointment_id);

        if ($stmt->execute()) {
            // marked as PAID
            return 0;
        } else {
            // some error
            return 1;
        }
    }

    // create new lab test request by user
    public function requestALabTest($patient_id, $details)
    {
        $stmt = $this->con->prepare("INSERT INTO `lab_tests` (`test_id`, `patient_id`, `details`, `test_status`) VALUES (NULL, ?, ?, 'PAID');");
        $stmt->bind_param("is", $patient_id, $details);

        if ($stmt->execute()) {
            // new lab test created
            return 0;
        } else {
            // some error
            return 1;
        }
    }

    /* CRUD  -> r -> RETRIEVE */

    // retreiving user data by username
    public function getUserByUsername($username)
    {
        $stmt = $this->con->prepare("SELECT * FROM `users` WHERE `username` = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // checking if the user exists
    private function isUserExist($username, $email)
    {
        $stmt = $this->con->prepare("SELECT `user_id` FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // retrieving the doctors for patient
    public function getDoctors()
    {
        $stmt = $this->con->prepare("SELECT `user_id`, `full_name`, `username` FROM `users` WHERE `user_type` = 'DOCTOR' AND `user_status` = 'ACTIVE'");
        $stmt->execute();
        return $stmt->get_result();
    }

    // getting the appointments table to the user
    public function getAppointments($user_id)
    {
        $stmt = $this->con->prepare("SELECT a.appointment_id, a.patient_id, a.doctor_id, a.description, a.date, a.time, a.appointment_status, a.comments, p.full_name  FROM appointments a JOIN users p ON p.user_id = a.doctor_id WHERE a.patient_id = ? ORDER BY a.appointment_id DESC ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();

    }

    // getting the payable appointments table to the user
    public function getPayableAppointmentsByUser($user_id)
    {
        $stmt = $this->con->prepare("SELECT * FROM `appointments` INNER JOIN `users` ON users.user_id = appointments.doctor_id WHERE `patient_id` = ? AND `appointment_status` = 'ACCEPTED'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();

    }

    // getting the appointments list for the history
    public function getAppointmentHistory($user_id)
    {
        $stmt = $this->con->prepare("SELECT * FROM `appointments` INNER JOIN `users` ON users.user_id = appointments.doctor_id WHERE `patient_id` = ? AND `appointment_status` = 'REJECTED' || `appointment_status` = 'COMPLETED' || `appointment_status` = 'CANCELLED'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();

    }

    // getting the incoming prescriptions table to the user
    public function getIncomingPrescriptionsByUser($user_id)
    {
        $stmt = $this->con->prepare("SELECT * FROM `prescriptions` WHERE `patient_id` = ? AND `prescription_status` = 'SHIPPED'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();

    }

    // getting the completed lab tests table to the user
    public function getCompletedLabTestsByUser($user_id)
    {
        $stmt = $this->con->prepare("SELECT * FROM `lab_tests` INNER JOIN `lab_reports` ON lab_reports.lab_test_id = lab_tests.test_id WHERE `patient_id` = ? AND `test_status` = 'COMPLETED'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();

    }

    // getting the ongoing lab tests table to the user
    public function getOngoingLabTestsByUser($user_id)
    {
        $stmt = $this->con->prepare("SELECT * FROM `lab_tests` WHERE `patient_id` = ? AND `test_status` = 'PAID' || `test_status` = 'ACCEPTED' ORDER BY `test_id` DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();

    }

    // getting the lab tests for the history
    public function getLabTestHistory($user_id)
    {
        $stmt = $this->con->prepare("SELECT * FROM `lab_tests` WHERE `patient_id` = ? AND `test_status` = 'COMPLETED' || `test_status` = 'CANCELLED'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();

    }

    /* CRUD  -> U -> UPDATE */

    // mark a prescription as received
    public function markAsReceived($prescription_id)
    {
        $stmt = $this->con->prepare("UPDATE `prescriptions` SET `prescription_status` = 'RECEIVED' WHERE `prescription_id` = ?");
        $stmt->bind_param("i", $prescription_id);

        if ($stmt->execute()) {
            // prescription received
            return 0;
        } else {
            // some error
            return 1;
        }
    }

    // update appointment description
    public function updateDescription($appointment_id, $description)
    {
        $stmt = $this->con->prepare("UPDATE `appointments` SET `description` = ? WHERE `appointment_id` = ?");
        $stmt->bind_param("si", $description, $appointment_id);

        if ($stmt->execute()) {
            // appointment updated
            return 0;
        } else {
            // some error
            return 1;
        }
    }

    // update lab test details
    public function updateLabTestDetails($lab_test_id, $details)
    {
        $stmt = $this->con->prepare("UPDATE `lab_tests` SET `details` = ? WHERE `test_id` = ?");
        $stmt->bind_param("si", $details, $lab_test_id);

        if ($stmt->execute()) {
            // lab test details updated
            return 0;
        } else {
            // some error
            return 1;
        }
    }

    // cancel a lab test by user
    public function cancelLabTest($lab_test_id)
    {
        $stmt = $this->con->prepare("UPDATE `lab_tests` SET `test_status` = 'CANCELLED' WHERE `test_id` = ?");
        $stmt->bind_param("i", $lab_test_id);

        if ($stmt->execute()) {
            // lab test cancelled
            return 0;
        } else {
            // some error
            return 1;
        }
    }

    // cancel an appointment by user
    public function cancelAppointment($appointment_id)
    {
        $stmt = $this->con->prepare("UPDATE `appointments` SET `appointment_status` = 'CANCELLED' WHERE `appointment_id` = ?");
        $stmt->bind_param("i", $appointment_id);

        if ($stmt->execute()) {
            // appointment cancelled
            return 0;
        } else {
            // some error
            return 1;
        }
    }

}
