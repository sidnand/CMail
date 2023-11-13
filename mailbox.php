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
?>
This is the mailbox page.

<div>
    <?php
        echo '<div>Mailbox: ' . $_SESSION['mailbox'] . '</div>';
        echo '<div>MailboxID: ' . $_SESSION['mailboxID'] . '</div>';
        echo '<div>Account: ' . $_SESSION['username'] . '</div>';

    ?>
</div>

<div class="logo">
    <img src="assets/logo.png">
</div>
</body>
</html>