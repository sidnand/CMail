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

        $error_message = "";
        $success_message = "";

        if (isset($_SESSION['userLoggedIn'])) {
            if (isset($_POST["logout"])) {
                session_unset();
                session_destroy();
                header('Location: index.php');
                exit;
            }
        }
    
        if (isset($_POST['back'])) {
            header('Location: account.php');
            exit;
        }

        if (isset($_POST['send-email'])) {
            $conn = connect_to_oracle();
            $sender = $_SESSION['username'];
            $recipient = $_POST['recepient'];
            $body = $_POST['body'];
            $attachment = $_FILES['attachment'];

            $result = send_email($conn, $sender, $recipient, $body, $attachment);

            if ($result) {
                $success_message = "Email sent successfully.";
            } else {
                $error_message = "Error sending email.";
            }

            oci_close($conn);
        }

        function send_email($conn, $sender, $recipient, $body, $attachment) {

            $mailboxID = get_mailbox_id($conn, $recipient);

            if ($attachment['name'] != "") {
                $full_name = $attachment["name"];
                $ext = end((explode(".", $full_name)));
                $name = pathinfo($full_name, PATHINFO_FILENAME);

                $query = "
                    INSERT ALL
                        INTO Email VALUES (email_sequence.nextval, '$sender', '$recipient', '$body', $mailboxID)
                        INTO Attachment VALUES ('$name', email_sequence.currval, '$ext')
                    SELECT * FROM dual";
            } else {
                $query = "INSERT INTO Email VALUES (email_sequence.nextval, '$sender', '$recipient', '$body', $mailboxID)";
            }

            $stmt = oci_parse($conn, $query);

            if (!$stmt) {
                $error_message = "Oracle Parse Error " . OCIError($conn)['message'];
                oci_close($conn);
                return false;
            }

            $result = oci_execute($stmt);

            if (!$result) {
                $error_message = "Oracle Execute Error " . OCIError($stmt)['message'];
                oci_rollback($conn);  // Rollback in case of error
                oci_close($conn);
                return false;
            }

            oci_free_statement($stmt);
            oci_close($conn);

            return true;
        }

        function get_next_email_id($conn) {
            $query = "SELECT email_sequence.nextval FROM dual";
            $stmt = oci_parse($conn, $query);

            if (!$stmt) {
                $error_message = "Oracle Parse Error " . OCIError($conn)['message'];
                oci_close($conn);
                return false;
            }

            $result = oci_execute($stmt);

            if (!$result) {
                $error_message = "Oracle Execute Error " . OCIError($stmt)['message'];
                oci_close($conn);
                return false;
            }

            $next_email_id = oci_fetch_assoc($stmt)['NEXTVAL'];

            oci_free_statement($stmt);

            return $next_email_id;
        }

        function get_mailbox_id($conn, $username) {
            $query = "SELECT mailboxID FROM Mailbox WHERE ownersUsername = '$username'";
            $stmt = oci_parse($conn, $query);

            if (!$stmt) {
                $error_message = "Oracle Parse Error " . OCIError($conn)['message'];
                oci_close($conn);
                return false;
            }

            $result = oci_execute($stmt);

            if (!$result) {
                $error_message = "Oracle Execute Error " . OCIError($stmt)['message'];
                oci_close($conn);
                return false;
            }

            $mailboxID = oci_fetch_assoc($stmt)['MAILBOXID'];

            oci_free_statement($stmt);
            oci_close($conn);

            return $mailboxID;
        }

        function get_emails() {
            $conn = connect_to_oracle();
            $mailboxID = (int) $_SESSION['mailboxID'];
            $query = "SELECT * FROM Email WHERE mailboxID = $mailboxID";
            $stmt = oci_parse($conn, $query);
            
            if (!$stmt) {
                $error_message = "Oracle Parse Error " . OCIError($conn)['message'];
                oci_close($conn);
                return false;
            }

            $result = oci_execute($stmt);

            if (!$result) {
                $error_message = "Oracle Execute Error " . OCIError($stmt)['message'];
                oci_close($conn);
                return false;
            }

            $emails = array();

            while ($row = oci_fetch_assoc($stmt)) {
                $emails[] = $row;
            }

            oci_free_statement($stmt);
            oci_close($conn);

            return $emails;
        }

        function get_attachment($email_id) {
            $conn = connect_to_oracle();
            $query = "SELECT * FROM Attachment WHERE emailID = $email_id";
            $stmt = oci_parse($conn, $query);

            if (!$stmt) {
                $error_message = "Oracle Parse Error " . OCIError($conn)['message'];
                oci_close($conn);
                return false;
            }

            $result = oci_execute($stmt);

            if (!$result) {
                $error_message = "Oracle Execute Error " . OCIError($stmt)['message'];
                oci_close($conn);
                return false;
            }

            $attachment = oci_fetch_assoc($stmt);

            oci_free_statement($stmt);
            oci_close($conn);

            return $attachment;
        }

        function display_emails($emails) {
            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered">';
            echo '<thead>';
            echo '<tr>';
            // echo '<th>Email ID</th>';
            echo '<th>From</th>';
            echo '<th>Body</th>';
            echo '<th>Attachment</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($emails as $email) {
                $attachment = get_attachment($email['EMAILID']);

                echo '<tr>';
                // echo '<td>' . $email['EMAILID'] . '</td>';
                echo '<td>' . $email['SENDER'] . '</td>';
                echo '<td>' . $email['BODY'] . '</td>';
                
                if ($attachment) {
                    echo '<td>' . $attachment['FILENAME'] . '.' . $attachment['FILETYPE'] . '</td>';
                }

                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
        }

        function get_email_count() {
            $mailboxID = (int) $_SESSION['mailboxID'];

            $conn = connect_to_oracle();
            $query = "SELECT COUNT(*) FROM Email WHERE mailboxID = $mailboxID";
            $stmt = oci_parse($conn, $query);

            if (!$stmt) {
                $error_message = "Oracle Parse Error " . OCIError($conn)['message'];
                oci_close($conn);
                return false;
            }

            $result = oci_execute($stmt);

            if (!$result) {
                $error_message = "Oracle Execute Error " . OCIError($stmt)['message'];
                oci_close($conn);
                return false;
            }

            $count = oci_fetch_assoc($stmt)['COUNT(*)'];

            oci_free_statement($stmt);
            oci_close($conn);

            echo $count;
        }


    ?>

    <div class="container">

        <div> <h1 class="header">Email Service</h1> </div>

        <?php
        
            if ($error_message != "") {
                echo "<div class='alert alert-danger' role='alert'>"
                    . $error_message
                    . "</div>";
            } else if ($success_message != "") {
                echo "<div class='alert alert-success' role='alert'>"
                    . $success_message
                    . "</div>";
            }
        
        ?>
        
        <div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title">Mailbox: <?php echo $_SESSION['mailbox'] ?></h2>
                <h4>This mailbox has: <?php get_email_count() ?> emails</h4>
                <div class="d-flex gap-4 pt-3">
                    <button type="button" class="btn btn-primary" id="composeEmailButton">Compose Email</button>

                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-group ms-auto">
                        <button type="submit" name="back" class="btn btn-danger">Back</button>
                        <button type="submit" name="logout" class="btn btn-danger">Logout</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="form-group card-body d-flex flex-column">
                <div class="input-group">
                    <input type="text" name="search_from" class="form-control" placeholder="Search where 'From' = " onclick="setActive(this)">
                    <input type="text" name="search_body" class="form-control" placeholder="Search where 'Body' = " onclick="setActive(this)">
                    <button type="submit" name="search-email" class="btn btn-primary">Search</button>
                </div>
                <br>
                <div style="display: inline-block">
                    <button type="button" class="btn btn-secondary" onclick="updateOperator('OR')">OR</button>
                    <button type="button" class="btn btn-secondary" onclick="updateOperator('AND')">AND</button>
                </div>
            </form>

            <hr>

            <div class="card-body d-flex flex-column">

                <?php
                    $emails = get_emails();

                    if ($emails !== false) {
                        display_emails($emails);
                    } else {
                        echo '<div class="alert alert-danger" role="alert">Error fetching emails.</div>';
                    }
                ?>

            </div>
        </div>
        

        <div>

            <div class="modal fade" id="compose-email" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" id="emailModalLabel">Compose Email</h5>
                        </div>

                        <div class="modal-body">
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="recipient">To:</label>
                                    <input type="text" name="recepient" class="form-control" id="recipient" required>
                                </div>

                                <br>
                                <div class="form-group">
                                    <label for="body">Email Body:</label>
                                    <textarea class="form-control" id="body" name="body" rows="4" placeholder="Enter email body" required></textarea>
                                </div>

                                <br>
                                <label for="attachment">Attachment:</label>
                                <input type="file" name="attachment" class="form-control" id="attachment">

                                <br>
                                <button type="submit" name="send-email" class="btn btn-primary">Send</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        



        <div class="logo">
            <img src="assets/logo.png">
        </div>
    
    </div>

<div>
    <?php
        // echo '<div>Mailbox: ' . $_SESSION['mailbox'] . '</div>';
        // echo '<div>MailboxID: ' . $_SESSION['mailboxID'] . '</div>';
        // echo '<div>Account: ' . $_SESSION['username'] . '</div>';

    ?>
</div>

    <script>
        var activeInput = null;

        document.getElementById('composeEmailButton').addEventListener('click', function () {
            var myModal = new bootstrap.Modal(document.getElementById('compose-email'));
            myModal.show();
        });

        function setActive(input) { activeInput = input; }

        function updateOperator(operator) {
            console.log(activeInput);

            if (activeInput != null) activeInput.value += ' ' + operator + ' ';
        }
    </script>

</body>
</html>