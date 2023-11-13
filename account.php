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
    $conn = connect_to_oracle();
    initMailboxes($conn);

    setAdminInfo($conn, $_SESSION['adminsNumber']);
    
    // Uncomment to reset mailboxes
    // clearMailboxes($conn);
    
    if (isset($_SESSION['userLoggedIn'])) {
        if (isset($_POST["logout"])) {
            session_unset();
            session_destroy();
            header('Location: index.php');
            exit;
        }
    }

    if (isset($_POST['view_mailbox'])) {
        $container = "select";
        $conn = connect_to_oracle();
        $mailboxes = get_mailboxes($conn);
    }

    if (isset($_POST['Back'])) {
        header('Location: user.php');
        exit;
    }

    if (isset($_POST['admin'])) {
        $popup = "admin";
    }

    if(isset($_POST['select_mailbox_submit'])) {
        $_SESSION['mailbox'] = $_POST['selected_mailbox'];
        $_SESSION['mailboxID'] = $_SESSION[$_SESSION['mailbox']];
        header('Location: mailbox.php');
    }

    function get_mailboxes($conn) {
        $query = "SELECT * FROM Mailbox WHERE ownersUsername = :username ORDER BY mailboxID";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":username", $_SESSION['username']);

        $result = oci_execute($stmt);
        if (!$result) {
            oci_close($conn);
            echo "<script>"
            . "console.log('Result = false!')"
            . "</script>";
            return false;
        }
    
        $mailboxes = array();
        while ($row = oci_fetch_assoc($stmt)) {
            $mailboxes[] = $row;
        }

        foreach ($mailboxes as $mailbox) {
            foreach ($mailbox as $key => $value) {
                //echo $key . ": " . $value . "<br>";
                if($key == "MAILBOXID") {
                    $_SESSION[$value] = "General";
                    $_SESSION["General"] = $value;
                }
            }
            //echo "------------------<br>"; // Separator for better readability
        }

        if (empty($mailboxes)) {
            $_SESSION["Initialized"] = false;
        } else {
            $_SESSION["Initialized"] = true;
        }
    
        return $mailboxes;
    }

    function mailboxExists($conn, $desiredMailboxID) {

        $query = "SELECT * FROM Mailbox WHERE mailboxID = :desiredMailboxID";
        $statement = oci_parse($conn, $query);

        oci_bind_by_name($statement, ":desiredMailboxID", $desiredMailboxID);

        $result = oci_execute($statement);

        if (!$result) {
            $error = oci_error($statement);
            die("Query execution error: " . $error['message']);
        }

        $row = oci_fetch_assoc($statement);

        // Check if the MailBoxID exists
        if ($row) {
            return true;
        } else {
            return false;
        }
    }

    function initMailbox($conn, $ID) {
        
        // Declare variables for query
        $mailboxID = $ID;
        $ownersUsername = $_SESSION['username'];

        $query = "INSERT INTO Mailbox VALUES (:mailboxID, :ownersUsername)";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":mailboxID", $mailboxID);
        oci_bind_by_name($stmt, ":ownersUsername", $ownersUsername);

        $result = oci_execute($stmt);
        if (!$result) {
            $error_message = "Oracle Execute Error " . oci_error($stmt)['message'];
            echo "Error message: " . $error_message;
            oci_close($conn);
            return false;
        }

        return true;
    }

    function initMailboxes($conn) {
        
        get_mailboxes($conn);
        if (!$_SESSION['Initialized']) {
            
            for($i = 0; $i < 10000; $i++) {
                if(!mailboxExists($conn, $i)) {
                    initMailbox($conn, $i);
                    break;
                }
            }
            $_SESSION['Initialized'] = true;
        }
    }

    function clearMailboxes($conn) {
        $query = "DELETE FROM Mailbox";

        // Prepare the SQL statement
        $stmt = oci_parse($conn, $query);

        // Execute the statement
        $result = oci_execute($stmt);

        if (!$result) {
            $error_message = "Oracle Execute Error " . oci_error($stmt)['message'];
            echo "Error message: " . $error_message;
            oci_close($conn);
            return false;
        }

        return true;
    }

    function setAdminInfo($conn, $num) {
        // Get Admin Name
        $query = "SELECT * FROM Admin WHERE agentNumber = $num";
        $stmt = oci_parse($conn, $query);

        $result = oci_execute($stmt);
        if (!$result) {
            oci_close($conn);
            return false;
        }

        if (oci_fetch($stmt)) {
            $_SESSION['admin_firstname'] = oci_result($stmt, "FIRSTNAME");
            $_SESSION['admin_lastname'] = oci_result($stmt, "LASTNAME");
            $_SESSION['admin_teamcode'] = oci_result($stmt, "WORKSFOR");
        }

        // Get Admin Team
        $teamCode = $_SESSION['admin_teamcode'];
        $query = "SELECT * FROM SupportTeam WHERE teamCode = $teamCode";
        $stmt = oci_parse($conn, $query);

        $result = oci_execute($stmt);
        if (!$result) {
            oci_close($conn);
            return false;
        }

        if (oci_fetch($stmt)) {
            $_SESSION['support_team_country'] = oci_result($stmt, "COUNTRY");
            $_SESSION['support_team_city'] = oci_result($stmt, "CITY");
        }

        return true;
    }
?>

<div class="container">
    <h1 class="header">Email Service</h1>
</div>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <button type="submit" class="btn btn-primary" name="admin" data-toggle="modal" data-target="#adminModal" style="position: fixed; top: 10px; right: 10px;">
        Check Admin
    </button>
</form>

<div class="container">
    <?php if (isset($_SESSION['userLoggedIn'])): ?>
    <div class="card mb-3">
        <div class="card-body d-flex flex-column">
            <h2 class="card-title">Account: <?php echo $_SESSION['account']; ?></h2>
            <div class="d-flex gap-4 pt-3">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <button type="submit" class="btn btn-primary" name="view_mailbox">View Mailboxes</button>
                </form>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-group ms-auto">
                    <button type="submit" name="Back" class="btn btn-danger">Back</button>
                    <button type="submit" name="logout" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </div>
    </div>
    <div id="select" style="display: none;">
        <div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title">Select A Mailbox</h2>
                <p></p>
            </div>
        </div>
    </div>
    <?php

    if ($container == "select") {
        echo '<div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title mb-3">Select Mailbox</h2>';

        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" class="row g-2">';
        
        echo '<div class="col-md-8">';
        echo '<select name="selected_mailbox" class="form-select">';
        
        foreach ($mailboxes as $mailbox) {
            foreach ($mailbox as $key => $value) {
                if($key == "MAILBOXID") {
                    echo '<option value="' . $_SESSION[$value] . '">';
                    echo $_SESSION[$value];
                    echo '</option>';
                }
            }
        }
        
        echo '</select>';
        echo '</div>';
        
        echo '<div class="col-md-4">';
        echo '<label>&nbsp;</label>';
        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
        echo '<input type="submit" name="select_mailbox_submit" value="Select Mailbox" class="btn btn-primary btn-block">';
        echo '</form>';
        echo '</div>';
        
        echo '</form>';
        echo '</div></div>';
    } 
    ?>


    <?php endif; ?>
</div>

<?php
    if ($popup == "admin") {
        // Display a pop-up for the "admin" container

        echo '<div id="adminPopup" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 40px; background-color: #fff; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); z-index: 1000;">
        <span onclick="closeAdminPopup()" style="position: absolute; top: 10px; right: 10px; cursor: pointer;">X</span>
        <p>';
        echo 'Admin: ';
        echo $_SESSION['admin_firstname'];
        echo ' ';
        echo $_SESSION['admin_lastname'];
        echo '</p>';
        echo '<p>';
        echo 'Team Location: ';
        echo $_SESSION['support_team_city'];
        echo ', ';
        echo $_SESSION['support_team_country'];
        echo '</p>';
        echo '</div>';

        // Trigger the adminPopup using JavaScript
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("adminPopup").style.display = "block";
        });

        function closeAdminPopup() {
            document.getElementById("adminPopup").style.display = "none";
        }
        </script>';
    }
?>

<div class="logo">
    <img src="assets/logo.png">
</div>
</body>
</html>