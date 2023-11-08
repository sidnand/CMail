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
        if (isset($_POST["logout"])) {
            session_unset();
            session_destroy();
            header('Location: index.php');
            exit;
        }
    }

    if(isset($_POST['select_account_submit'])) {
        $conn = connect_to_oracle();
        $_SESSION['account'] = $_POST['selected_account'];
        $account = get_account($conn, $_SESSION['account']);
        $_SESSION['dateCreated'] = $account['dateCreated'];
        $_SESSION['ownersPhoneNumber'] = $account['ownersPhoneNumber'];
        $_SESSION['adminsNumber'] = $account['adminsNumber'];

        header('Location: account.php');
    }

    if (isset($_POST['select_account'])) {
        $container = "select";
        $conn = connect_to_oracle();
        $accounts = get_accounts($conn, $_SESSION['phoneNumber']);
    }

    if (isset($_POST['delete_account']) || isset($_POST['delete_account_submit'])) {
        $container = "delete";
        $conn = connect_to_oracle();
        $accounts = get_accounts($conn, $_SESSION['phoneNumber']);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account_submit'])) {
        $conn = connect_to_oracle();
        $username = $_POST["deleted_account"];
        $deleted = delete_account($conn, $username);

        if (!$deleted) {
            $error_message_delete = "Error deleting the account.";
        } else {
            $success_message_delete = "Success! Account has been deleted.";
            $accounts = get_accounts($conn, $_SESSION['phoneNumber']);
        }
        oci_close($conn);
    }

    if (isset($_POST['add_account']) || isset($_POST['add_account_submit'])) {
        $container = "add";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_account_submit'])) {    
        $conn = connect_to_oracle();
        $username = $_POST["username"];
        $account = get_account($conn, $username);

        if ($account) {
            $error_message = "Username already exists. Please choose another.";
        } else {
            $result = insert_account($conn, $username);
            if (!$result) {
                $error_message = "Error creating user.";
            } else {
                $success_message = "Success! Account has been created.";
            }
        }
        oci_close($conn);
    }

    function insert_account($conn, $username) {
        $adminsNumber = rand(1010, 1014);
        $ownersPhoneNumber = $_SESSION['phoneNumber'];
        $currentDate = new DateTime();
        $formattedDate = $currentDate->format("Y-m-d");

        $query = "INSERT INTO Account VALUES (:username, TO_DATE(:currentDate, 'YYYY-MM-DD'), :ownersPhoneNumber, :adminsNumber)";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":username", $username);
        oci_bind_by_name($stmt, ":currentDate", $formattedDate);
        oci_bind_by_name($stmt, ":ownersPhoneNumber", $ownersPhoneNumber);
        oci_bind_by_name($stmt, ":adminsNumber", $adminsNumber);

        $result = oci_execute($stmt);
        if (!$result) {
            $error_message = "Oracle Execute Error " . oci_error($stmt)['message'];
            echo "Error message: " . $error_message;
            oci_close($conn);
            return false;
        }
        return true;
    }

    function get_account($conn, $username) {
        $query = "SELECT * FROM Account WHERE username = :username";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":username", $username);

        $result = oci_execute($stmt);
        if (!$result) {
            oci_close($conn);
            return false;
        }

        if (oci_fetch($stmt)) {
            return array(
                "username" => oci_result($stmt, "USERNAME"),
                "dateCreated" => oci_result($stmt, "DATECREATED"),
                "ownersPhoneNumber" => oci_result($stmt, "OWNERSPHONENUMBER"),
                "adminsNumber" => oci_result($stmt, "ADMINSNUMBER")
            );
        }
        return false;
    }

    function get_accounts($conn, $phone_number) {
        $query = "SELECT * FROM Account WHERE ownersPhoneNumber = :phone_number";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":phone_number", $phone_number);

        $result = oci_execute($stmt);
        if (!$result) {
            oci_close($conn);
            return false;
        }
    
        $accounts = array();
        while ($row = oci_fetch_assoc($stmt)) {
            $accounts[] = $row;
        }
    
        return $accounts;
    }

    function delete_account($conn, $username) {
        $query = "DELETE FROM Account WHERE username = :username";
    
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":username", $username);
    
        $result = oci_execute($stmt);
    
        if ($result) {
            return true;
        } else {
            $error_message = "Oracle Execute Error " . oci_error($stmt)['message'];
            echo "Error message: " . $error_message;
            oci_close($conn);
            return false;
        }
    }
?>
<div class="container">
    <h1 class="header">Email Service</h1>
</div>

<div class="container">
    <?php if (isset($_SESSION['userLoggedIn'])): ?>
    <div class="card mb-3">
        <div class="card-body d-flex flex-column">
            <h2 class="card-title">Welcome to your home page, <?php echo $_SESSION['firstName'] . ' ' . $_SESSION['lastName']; ?></h2>
            <div class="d-flex gap-4 pt-3">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <button type="submit" class="btn btn-primary" name="select_account">Select An Account</button>
                </form>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <button type="submit" class="btn btn-primary" name="add_account">Add An Account</button>
                </form>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <button type="submit" class="btn btn-primary" name="delete_account">Delete Account</button>
                </form>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-group ms-auto">
                    <button type="submit" name="logout" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </div>
    </div>
    <div id="select" style="display: none;">
        <div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title">Select An Account</h2>
                <p></p>
            </div>
        </div>
    </div>
    <?php
    if ($container == "add") {
        echo '<div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title mb-3">Add Account</h2>';

        if ($error_message != "") {
            echo "<div class='alert alert-danger' role='alert'>"
                . $error_message
                . "</div>";
        }

        if ($success_message != "") {
            echo "<div class='alert alert-success' role='alert'>"
                . $success_message
                . "</div>";
        }

        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
                <div class="row">
                    <div class="col">
                        <input type="username" name="username" class="form-control" id="username" placeholder="Username" required>
                    </div>
                    <div class="col">
                        <input type="submit" name="add_account_submit" class="btn btn-success" value="Create Account">
                    </div>
                </div>
            </form>
        </div>
    </div>';
    }

    if ($container == "select") {
        echo '<div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title mb-3">Select an Account</h2>';

        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" class="row g-2">';
        
        echo '<div class="col-md-8">';
        echo '<select name="selected_account" class="form-select">';
        
        foreach ($accounts as $account) {
            echo '<option value="' . $account['USERNAME'] . '">';
            echo $account['USERNAME'];
            echo '</option>';
        }
        
        echo '</select>';
        echo '</div>';
        
        echo '<div class="col-md-4">';
        echo '<label>&nbsp;</label>';
        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
        echo '<input type="submit" name="select_account_submit" value="Select Account" class="btn btn-primary btn-block">';
        echo '</form>';
        echo '</div>';
        
        echo '</form>';
        echo '</div></div>';
    }

    if ($container == "delete") {
        echo '<div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title mb-3">Delete an Account</h2>';
        
        if ($error_message_delete != "") {
            echo "<div class='alert alert-danger' role='alert'>"
                . $error_message_delete
                . "</div>";
        }

        if ($success_message_delete != "") {
            echo "<div class='alert alert-success' role='alert'>"
                . $success_message_delete
                . "</div>";
        }

        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" class="row g-2">';
        
        echo '<div class="col-md-8">';
        echo '<select name="deleted_account" class="form-select">';
        
        foreach ($accounts as $account) {
            echo '<option value="' . $account['USERNAME'] . '">';
            echo $account['USERNAME'];
            echo '</option>';
        }
        
        echo '</select>';
        echo '</div>';
        
        echo '<div class="col-md-4">';
        echo '<label>&nbsp;</label>';
        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
        echo '<input type="submit" name="delete_account_submit" value="Delete Account" class="btn btn-danger btn-block">';
        echo '</form>';
        echo '</div>';
        
        echo '</form>';
        echo '</div></div>';
    }

    ?>
    <div id="delete" style="display: none;">
        <div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title">Add An Account</h2>
                <p></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<div class="logo">
    <img src="assets/logo.png">
</div>
</body>
</html>