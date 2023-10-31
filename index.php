<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Service</title>
</head>
<body>
    <?php
        include('base.php');

        $error_message = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            if ($_POST["login"]) {
                $conn = connectToOracle();
                $phone_number = $_POST["phone-number"];

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
                    $firstname = oci_result($stmt, "FIRSTNAME");
                    $lastname = oci_result($stmt, "LASTNAME");
                    $phone_number = oci_result($stmt, "PHONENUMBER");

                    $_SESSION['userLoggedIn'] = true;
                    $_SESSION['firstName'] = $firstname;
                    $_SESSION['lastName'] = $lastname;

                    header('Location: user.php');

                    oci_close($conn);
                } else {
                    $error_message = "User not found, please create an account";
                    oci_close($conn);
                }


            } else if (isset($_POST["signup"])) {
                $conn = connectToOracle();
                echo "Signup";
            }

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
</body>
</html>