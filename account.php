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
This is the account page.

<div>
    <?php
        echo '<div>Account is: ' . $_SESSION['account'] . '</div>';
        echo '<div>Date Created is: ' . $_SESSION['dateCreated'] . '</div>';
        echo '<div>Owners Phone Number is: ' . $_SESSION['ownersPhoneNumber'] . '</div>';
        echo '<div>Admins Number is: ' . $_SESSION['adminsNumber'] . '</div>';
    ?>
</div>

<div class="logo">
    <img src="assets/logo.png">
</div>
</body>
</html>