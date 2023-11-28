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
    
    // Uncomment to reset mailboxes for user
    // clearMailboxes($conn, $_SESSION['username']);
    
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
        $displayCount = false;
    }

    if (isset($_POST['move_emails']) || isset($_POST['move_emails_submit'])) {
        $container = "move";
        $conn = connect_to_oracle();
        $mailboxes = get_mailboxes($conn);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['move_emails_submit'])) {
        
        $mailboxID1 = $_SESSION[$_POST["selected_mailbox1"]];
        $mailboxID2 = $_SESSION[$_POST["selected_mailbox2"]];
        $conn = connect_to_oracle();

        if($mailboxID1 == $mailboxID2) {
            $error_message = "Cannot move emails to the same mailbox.";
        }
        else {
            $result = moveEmails($conn, $mailboxID1, $mailboxID2);
            if (!$result) {
                $error_message = "Error Moving Emails";
            } else {
                $success_message = "Successfully Moved Emails!";
            }
            oci_close($conn);
        }

    }

    if (isset($_POST['create_custom_mailbox']) || isset($_POST['create_custom_mailbox_submit'])) {
        $container = "create";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_custom_mailbox_submit'])) {
        $conn = connect_to_oracle();
        $customLabel = $_POST["custom_mailbox_label"];
        $customMailbox = get_custom_mailbox($conn, $customLabel);

        if ($customMailbox) {
            $error_message = "Mailbox with selected name already exists. Please enter a new name.";
        } else {
            $result = insert_custom_mailbox($conn, $customLabel); 
            if (!$result) {
                $error_message = "Error creating mailbox.";
            } else {
                $success_message = "Success! Mailbox has been created.";
            }
        }
        oci_close($conn);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['select_mailbox_get_status'])) {
        $container = "select";
        $conn = connect_to_oracle();
        $mailboxes = get_mailboxes($conn);
        $mailboxName = $_POST['selected_mailbox'];
        $_SESSION['mailbox'] = $_POST['selected_mailbox'];

        $result = get_mailbox_status($conn, $mailboxName);
        if (!$result) {
            $error_message = "Error getting status.";
        } else {
            $success_message = $result;
        }

    }

    if (isset($_POST['delete_custom_mailbox']) || isset($_POST['delete_custom_mailbox_submit'])) {
        $container = "delete";
        $conn = connect_to_oracle();
        $mailboxes = get_mailboxes($conn);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_custom_mailbox_submit'])) {
        $conn = connect_to_oracle();
        $customLabel = $_POST["deleted_mailbox"];

        if($customLabel == 'General') {
            $error_message_delete = "Cannot delete General Mailbox";
        } else {
            $deleted = delete_custom_mailbox($conn, $customLabel);
            if (!$deleted) {
                $error_message_delete = "Error deleting the account.";
            } else {
                $success_message_delete = "Success! Account has been deleted.";
                $mailboxes = get_mailboxes($conn);
            }
        }
        oci_close($conn);
    }

    if (isset($_POST['Back'])) {
        header('Location: user.php');
        exit;
    }

    if (isset($_POST['admin'])) {
        $popup = "admin";
    }

    if (isset($_POST['check_mailbox_status'])) {
        
        $conn = connect_to_oracle();

        $query = "SELECT mailboxID
        FROM Email
        WHERE recipient = :username
        GROUP BY mailboxID
        HAVING COUNT(emailID) > 0";

        $stmt = oci_parse($conn, $query);

        $username = $_SESSION['username'];
        oci_bind_by_name($stmt, ":username", $username);

        $result = oci_execute($stmt);

        if (!$result) {
            $error = oci_error($stmt);
            oci_close($conn);
            echo "<script>"
            . "console.log('Error: " . htmlentities($error['message']) . "')"
            . "</script>";
            return false;
        }

        while ($row = oci_fetch_assoc($stmt)) {
            $IDArray[] = $row['MAILBOXID']; 
        }
        
        $popup = "status";
    }

    if(isset($_POST['select_mailbox_submit'])) {
        $_SESSION['mailbox'] = $_POST['selected_mailbox'];
        $_SESSION['mailboxID'] = $_SESSION[$_SESSION['mailbox']];
        header('Location: mailbox.php');
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['get_email_count'])) {

        $container = "select";
        $conn = connect_to_oracle();
        $mailboxes = get_mailboxes($conn);

        $selectedMailbox = $_POST['selected_mailbox'];
        $emailCount = get_email_count($selectedMailbox);
        $displayCount = true;

    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['get_senders'])) {

        $container = "select";
        $conn = connect_to_oracle();
        $mailboxes = get_mailboxes($conn);

        $selectedMailbox = $_POST['selected_mailbox'];
        //echo $_SESSION[$selectedMailbox];
        $senders = get_senders($conn, $selectedMailbox);
        $popup = "senders";

    }

    function get_senders($conn, $label) {
        
        //echo $label;

        if($label == 'General') {
            $query = "SELECT sender FROM Email
            WHERE mailboxID = :mailboxID";

            $stmt = oci_parse($conn, $query);
            oci_bind_by_name($stmt, ":mailboxID", $_SESSION[$label]);
            
        } else {
            $query = "SELECT sender FROM CustomMailbox
            JOIN Email ON CustomMailbox.mailboxID = Email.mailboxID
            WHERE customLabel = :label";

            $stmt = oci_parse($conn, $query);
            oci_bind_by_name($stmt, ":label", $label);
        }

        $result = oci_execute($stmt);

        if (!$result) {
            $error = oci_error($stmt);
            oci_close($conn);
            echo "<script>"
            . "console.log('Error: " . htmlentities($error['message']) . "')"
            . "</script>";
            return false;
        }

        $senders = array();
        while ($row = oci_fetch_assoc($stmt)) {
            $senders[] = $row;
        }

        return $senders;

    }

    function moveEmails($conn, $mailboxID1, $mailboxID2) {

        $query = "UPDATE Email
        SET Email.mailboxID = :newMailboxID
        WHERE Email.mailboxID = :currentMailboxID";
        
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":currentMailboxID", $mailboxID1);
        oci_bind_by_name($stmt, ":newMailboxID", $mailboxID2);

        $result = oci_execute($stmt);

        if (!$result) {
            $error = oci_error($stmt);
            oci_close($conn);
            echo "<script>"
            . "console.log('Error: " . htmlentities($error['message']) . "')"
            . "</script>";
            return false;
        }

        return true;

    }

    function delete_custom_mailbox($conn, $customLabel) {
        
        $customMailbox = get_custom_mailbox($conn, $customLabel);

        $mailboxID = $customMailbox['MAILBOXID'];

        $query = "DELETE FROM CustomMailbox WHERE mailboxID = :mailboxID";

        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":mailboxID", $mailboxID);

        $result = oci_execute($stmt);

        if (!$result) {
            $error = oci_error($stmt);
            oci_close($conn);
            echo "<script>"
            . "console.log('Error: " . htmlentities($error['message']) . "')"
            . "</script>";
            return false;
        }

        $query = "DELETE FROM Mailbox WHERE mailboxID = :mailboxID";
        $stmt = oci_parse($conn, $query);

        oci_bind_by_name($stmt, ":mailboxID", $mailboxID);

        $result = oci_execute($stmt);
        if (!$result) {
            $error = oci_error($stmt);
            oci_close($conn);
            echo "<script>"
                . "console.log('2Error: " . htmlentities($error['message']) . "')"
                . "</script>";
            return false;
        }

        return true;
    }

    function get_custom_mailbox($conn, $customLabel) {
        $query = "SELECT * FROM CustomMailbox WHERE customLabel = :customLabel AND ownersUsername = :ownersUsername";
        $stmt = oci_parse($conn, $query);

        oci_bind_by_name($stmt, ":customLabel", $customLabel);
        oci_bind_by_name($stmt, ":ownersUsername", $_SESSION['username']);

        $result = oci_execute($stmt);
        if (!$result) {
            oci_close($conn);
            echo "<script>"
            . "console.log('get_custom_mailbox_error!')"
            . "</script>";
            return false;
        }

        $row = oci_fetch_assoc($stmt);

        return $row;
    }

    function insert_custom_mailbox($conn, $customLabel) {
        
        for($i = 13; $i < 10000; $i++) {
            if(!mailboxExists($conn, $i)) {
                $mailboxID = $i;
                break;
            }
        }
        $ownersUsername = $_SESSION['username'];
        $currentDate = new DateTime();
        $formattedDate = $currentDate->format("Y-m-d");

        $query = "INSERT INTO CustomMailbox VALUES (:mailboxID, :ownersUsername, TO_DATE(:currentDate, 'YYYY-MM-DD'), :customLabel)";
        $stmt = oci_parse($conn, $query);

        oci_bind_by_name($stmt, ":mailboxID", $mailboxID);
        oci_bind_by_name($stmt, ":ownersUsername", $ownersUsername);
        oci_bind_by_name($stmt, ":currentDate", $formattedDate);
        oci_bind_by_name($stmt, ":customLabel", $customLabel);

        $result = oci_execute($stmt);
        if (!$result) {
            $error = oci_error($stmt);
            oci_close($conn);
            echo "<script>"
                . "console.log('Error: " . htmlentities($error['message']) . "')"
                . "</script>";
            return false;
        }
        // Insert into mailbox
        $query = "INSERT INTO Mailbox VALUES (:mailboxID, :ownersUsername)";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":mailboxID", $mailboxID);
        oci_bind_by_name($stmt, ":ownersUsername", $ownersUsername);

        $result = oci_execute($stmt);
        if (!$result) {
            $error_message = "2Oracle Execute Error " . oci_error($stmt)['message'];
            echo "Error message: " . $error_message;
            oci_close($conn);
            return false;
        }

        return true;
    }

    function getLabel($conn,$value) {
        $query = "SELECT customLabel FROM CustomMailbox WHERE mailboxID = :mailboxID";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":mailboxID", $value);

        $result = oci_execute($stmt);
        if (!$result) {
            oci_close($conn);
            echo "<script>"
            . "console.log('Result = false!')"
            . "</script>";
            return false;
        }

        $row = oci_fetch_assoc($stmt);
        $label = $row['CUSTOMLABEL'];

        return $label;
    }

    function get_mailboxes($conn) {
        $query = "SELECT mailboxID FROM Mailbox WHERE ownersUsername = :username UNION
        SELECT mailboxID FROM CustomMailbox WHERE ownersUsername = :username ORDER BY mailboxID";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ":username", $_SESSION['username']);

        $result = oci_execute($stmt);
        if (!$result) {
            $error = oci_error($stmt);
            oci_close($conn);
            echo "<script>"
                . "console.log('Error: " . htmlentities($error['message']) . "')"
                . "</script>";
            return false;
        }
    
        $mailboxes = array();
        while ($row = oci_fetch_assoc($stmt)) {
            $mailboxes[] = $row;
        }

        $i = 0;
        foreach ($mailboxes as $mailbox) {
            foreach ($mailbox as $key => $value) {
                //echo $key . ": " . $value . "<br>";
                if($key == "MAILBOXID") {
                    if($i == 0) {
                        $_SESSION[$value] = "General";
                        $_SESSION["General"] = $value;
                        $i = $i + 1;
                    } else {
                        $customLabel = getLabel($conn,$value);
                        if (!$customLabel) {
                            oci_close($conn);
                            return false;
                        }
                        $_SESSION[$value] = $customLabel;
                        $_SESSION[$customLabel] = $value;
                    }
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

        $query = "SELECT mailboxID FROM Mailbox WHERE mailboxID = :desiredMailboxID UNION ALL
        SELECT mailboxID FROM CustomMailbox WHERE mailboxID = :desiredMailboxID";
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

    function clearMailboxes($conn, $username) {
        $query = "DELETE FROM CustomMailbox WHERE ownersUsername = :ownersUsername";

        // Prepare the SQL statement
        $stmt = oci_parse($conn, $query);

        oci_bind_by_name($stmt, ":ownersUsername", $username);

        // Execute the statement
        $result = oci_execute($stmt);

        if (!$result) {
            $error_message = "Oracle Execute Error " . oci_error($stmt)['message'];
            echo "Error message: " . $error_message;
            oci_close($conn);
            return false;
        }

        $query = "DELETE FROM Mailbox WHERE ownersUsername = :ownersUsername";

        // Prepare the SQL statement
        $stmt = oci_parse($conn, $query);

        oci_bind_by_name($stmt, ":ownersUsername", $username);

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


    function get_all_email_counts($conn) {
        $username = $_SESSION['username'];
        $query = "SELECT mailboxID, COUNT(*) as EMAIL_COUNT FROM Email WHERE recipient = '$username' GROUP BY mailboxID ORDER BY mailboxID";
        $stmt = oci_parse($conn, $query);

        $result = oci_execute($stmt);
        if (!$result) {
            oci_close($conn);
            echo "<script>"
            . "console.log('Result = false!')"
            . "</script>";
            return false;
        }

        $rows = array();

        while ($row = oci_fetch_assoc($stmt)) {
            $rows[] = $row;
            echo $row['EMAIL_COUNT'];
        }

        return $rows;
    }


    function get_email_count($label) {

        $conn = connect_to_oracle();

        $count = 0;
        $all = get_all_email_counts($conn);
        $mailboxID = $_SESSION[$label];

        if($label == 'General') {
            $count = $all[0]['EMAIL_COUNT'];
        } else {
            $query = "SELECT mailboxID from Mailbox WHERE customLabel = :label";

            $stmt = oci_parse($conn, $query);

            oci_bind_by_name($stmt, ":label", $label);
            
            if (!$stmt) {
                $error = oci_error($stmt);
                oci_close($conn);
                echo "<script>"
                    . "console.log('1Error: " . htmlentities($error['message']) . "')"
                    . "</script>";
                return false;
            }

            $result = oci_execute($stmt);

            if (!$result) {
                $error = oci_error($stmt);
                oci_close($conn);
                echo "<script>"
                    . "console.log('2Error: " . htmlentities($error['message']) . "')"
                    . "</script>";
                return false;
            }

            $row = oci_fetch_assoc($stmt);

            $mailboxID = $row['MAILBOXID'];

            if ($row && isset($all)) {
                foreach ($all as $a) {
                    if ($a['MAILBOXID'] == $mailboxID) {
                        $count = $a['EMAIL_COUNT'];
                        break;
                    }
                }
            }
        }

        // if($label == 'General') {
        //     $count = $row['COUNT(*)'][0];
        // } else {
        //     $query = "SELECT COUNT(*) FROM CustomMailbox
        //     JOIN Email ON CustomMailbox.mailboxID = Email.mailboxID
        //     WHERE customLabel = :label";
        // }

        $displayCount = true;
        return $count;

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

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <button type="submit" class="btn btn-primary" name="check_mailbox_status" style="position: fixed; top: 10px; right: 150px;">Check Mailbox Status</button>
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

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <button type="submit" class="btn btn-primary" name="create_custom_mailbox">Create Mailbox</button>
                </form>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <button type="submit" class="btn btn-primary" name="delete_custom_mailbox">Delete Mailbox</button>
                </form>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <button type="submit" class="btn btn-primary" name="move_emails">Move Emails</button>
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

        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" class="row g-2">';
        
        echo '<div class="d-flex gap-4 pt-3">';
        echo '<select name="selected_mailbox" class="form-select" style="width: 500px;>';

        echo '<option value="' . "General" . '">';
        echo "General";
        echo '</option>';
        
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
        if($displayCount) {
            echo $emailCount;
            if($emailCount == 1) {
                echo " email in ";
            } else {
                echo " emails in ";
            }
            echo $selectedMailbox;
        }
        echo '<label>&nbsp;</label>';
        echo '<input type="submit" name="select_mailbox_submit" value="Select Mailbox" class="btn btn-primary btn-block">';
        echo '<input type="submit" name="get_email_count" value="Get Email Count" class="btn btn-primary btn-block">';
        echo '<input type="submit" name="get_senders" value="List Correspondents in Mailbox" class="btn btn-primary btn-block">';
        echo '</div>';
        
        echo '</form>';
        echo '</div></div>';
    }

    if ($container == "move") {
        echo '<div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title mb-3">Move Emails</h2>';

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

        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" class="row g-2">';
        
        echo '<div class="d-flex gap-4 pt-3">';
        echo 'Move All Emails From ';
        echo '<select name="selected_mailbox1" class="form-select" style="width: 250px;">';
        
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
        echo ' to ';
        echo '<select name="selected_mailbox2" class="form-select" style="width: 250px;>';
        
        echo '<option value="' . "General" . '">';
        echo "General";
        echo '</option>';
        
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

        echo '<label>&nbsp;</label>';
        echo '<input type="submit" name="move_emails_submit" value="Move" class="btn btn-primary btn-block">';
        echo '</div>';
        
        echo '</form>';
        echo '</div></div>';
    }
    
    if ($container == "create") {
        echo '<div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title mb-3">Add Custom Mailbox</h2>';

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
                        <input type="custom_mailbox_label" name="custom_mailbox_label" class="form-control" id="custom_mailbox_label" placeholder="Mailbox Label" required>
                    </div>
                    <div class="col">
                        <input type="submit" name="create_custom_mailbox_submit" class="btn btn-success" value="Create Mailbox">
                    </div>
                </div>
            </form>
        </div>
    </div>';
    }

    if ($container == "delete") {
        echo '<div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title mb-3">Delete a Custom Mailbox</h2>';
        
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
        echo '<select name="deleted_mailbox" class="form-select">';
        
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
        echo '<input type="submit" name="delete_custom_mailbox_submit" value="Delete Mailbox" class="btn btn-danger btn-block">';
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
    if ($popup == "status") {
        // Display a pop-up for the "admin" container

        echo '<div id="adminPopup" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 40px; background-color: #fff; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); z-index: 1000;">
        <span onclick="closeAdminPopup()" style="position: absolute; top: 10px; right: 10px; cursor: pointer;">X</span>
        <p>';
        if($IDArray == NULL) {
            echo 'No Mailboxes Currently in Use';
        } else {
            echo 'Mailboxes Currently in Use:';
            echo '<br>';
            foreach($IDArray as $ID) {
                echo '<br>';
                echo $_SESSION[$ID];
            }
        }
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
    if ($popup == "senders") {
        // Display a pop-up for the "admin" container

        echo '<div id="adminPopup" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 40px; background-color: #fff; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); z-index: 1000;">
        <span onclick="closeAdminPopup()" style="position: absolute; top: 10px; right: 10px; cursor: pointer;">X</span>
        <p>';
        echo "List of correspondents in ";
        echo $selectedMailbox;
        echo ":";
        echo '<br>';
        foreach($senders as $sender) {
            echo '<br>';
            echo $sender['SENDER'];
        }
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