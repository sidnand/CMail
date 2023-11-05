<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Service</title>
    <style>
        .logo img {
            width: 120px;
            height: 60px;
            position: fixed;
            bottom: 0;
            right: 0;
        }
    </style>
</head>
<body>
    <?php
        include('base.php');

        if (isset($_SESSION['userLoggedIn'])) {
            header('Location: user.php');
        }

        $error_message = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            if (isset($_POST["login"])) {
                $conn = connect_to_oracle();
                $phone_number = $_POST["phone-number"];

                if (!is_numeric($phone_number)) {
                    $error_message = "Phone number must be a number";
                    oci_close($conn);
                } else {

                    $user = get_user($conn, $phone_number);

                    if (!$user) {
                        $error_message = "User not found, please create an account";
                        oci_close($conn);
                    } else {
                        $_SESSION['userLoggedIn'] = true;
                        $_SESSION['firstName'] = $user['firstname'];
                        $_SESSION['lastName'] = $user['lastname'];
                        $_SESSION['phoneNumber'] = $user['phone_number'];

                        header('Location: user.php');
                    }

                }

            } else if (isset($_POST["signup"])) {
                $conn = connect_to_oracle();
                
                $firstname = $_POST["firstname"];
                $lastname = $_POST["lastname"];
                $phone_number = $_POST["phone-number"];

                if (!is_numeric($phone_number)) {
                    $error_message = "Phone number must be a number";
                    oci_close($conn);
                } else {

                    $user = get_user($conn, $phone_number);

                    if ($user) {
                        $error_message = "User already exists";
                        oci_close($conn);
                        // return false;
                    } else {

                        $result = insert_user($conn, $firstname, $lastname, $phone_number);

                        if (!$result) {
                            $error_message = "Error creating user";
                            oci_close($conn);
                            // return false;
                        } else {
                            $_SESSION['userLoggedIn'] = true;
                            $_SESSION['firstName'] = $firstname;
                            $_SESSION['lastName'] = $lastname;
                            $_SESSION['phoneNumber'] = $phone_number;

                            header('Location: user.php');
                        }

                    }

                }
            }

        }

        function get_user($conn, $phone_number) {
            $query = "SELECT * FROM \"User\" WHERE phoneNumber = :phone_number";
            $stmt = oci_parse($conn, $query);
            oci_bind_by_name($stmt, ":phone_number", $phone_number);
            
            $result = oci_execute($stmt);

            if (!$result) {
                $error_message = "Oracle Execute Error " . OCIError($stmt)['message'];
                oci_close($conn);
                return false;
            }

            if (oci_fetch($stmt)) {
                
                return array(
                    "firstname" => oci_result($stmt, "FIRSTNAME"),
                    "lastname" => oci_result($stmt, "LASTNAME"),
                    "phone_number" => oci_result($stmt, "PHONENUMBER")
                );

            }

            return false;
        }

        function insert_user($conn, $first_name, $last_name, $phone_number) {
            $query = "INSERT INTO \"User\" VALUES (:phone_number, :first_name, :last_name)";
            $stmt = oci_parse($conn, $query);
            oci_bind_by_name($stmt, ":phone_number", $phone_number);
            oci_bind_by_name($stmt, ":first_name", $first_name);
            oci_bind_by_name($stmt, ":last_name", $last_name);

            $result = oci_execute($stmt);

            if (!$result) {
                $error_message = "Oracle Execute Error " . oci_error($stmt)['message'];
                oci_close($conn);
                return false;
            }

            return true;
        }
    ?>


    <div class="container">
        <h1 class="header">Email Service</h1>
        <?php
        
            if ($error_message != "") {
                echo "<div class='alert alert-danger' role='alert'>"
                    . $error_message
                    . "</div>";
            }
        
        ?>

        <div class="row">
            <div class="col">

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-group">
                    <h3>Login</h3>
                    <br>

                    <div class="row">
                        <div class="col">
                            <input type="phone-number" name="phone-number" class="form-control" id="phone-number" placeholder="Phone number" required>
                        </div>
                    </div>

                    <br>

                    <input type="submit" name="login" class="btn btn-primary" value="Login">
                </form>

            </div>

            <div class="col">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-group">
                    <h3>Signup</h3>
                    <br>

                    <div class="row">
                        <div class="col">
                            <input type="firstname" name="firstname" class="form-control" id="firstname" placeholder="Firstname" required>
                        </div>
                        <div class="col">
                            <input type="lastname" name="lastname" class="form-control" id="lastname" placeholder="Lastname" required>
                        </div>
                    </div>

                    <br>

                    <div class="row">
                        <div class="col">
                            <input type="phone-number" name="phone-number" class="form-control" id="phone-number" placeholder="Phone number" required>
                        </div>
                    </div>

                    <br>

                    <input type="submit" name="signup" class="btn btn-primary" value="Signup">
                </form>


            </div>
        </div>

    </div>

    <div class="logo">
        <img src="assets/logo.png">
    </div>
    
</html>