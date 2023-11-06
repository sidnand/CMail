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
?>

<div class="container">
    <h1 class="header">Email Service</h1>
</div>

<div class="container">
    <?php if (isset($_SESSION['userLoggedIn'])): ?>
    <div class="card">
        <div class="card-body d-flex flex-column">
            <h2 class="card-title">Welcome to your account, <?php echo $_SESSION['firstName'] . ' ' . $_SESSION['lastName']; ?></h2>
            <div class="text-end mt-3">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-group">
                    <button type="submit" name="logout" class="btn btn-danger">Logout</button>
                </form>
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