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
    <div class="card mb-3">
        <div class="card-body d-flex flex-column">
            <h2 class="card-title">Welcome to your account, <?php echo $_SESSION['firstName'] . ' ' . $_SESSION['lastName']; ?></h2>
            <div class="d-flex gap-4 pt-3">
                <form method="post">
                    <button type="button" class="btn btn-primary" name="select" onclick="toggleCard('select')">Select An Account</button>
                </form>

                <form method="post">
                    <button type="button" class="btn btn-primary" name="add" onclick="toggleCard('add')">Add Account</button>
                </form>

                <form method="post">
                    <button type="button" class="btn btn-primary" name="delete" onclick="toggleCard('delete')">Delete Account</button>
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
                <h2 class="card-title">Second Card Content</h2>
                <p></p>
            </div>
        </div>
    </div>
    <div id="add" style="display: none;">
        <div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title">third Card Content</h2>
                <p></p>
            </div>
        </div>
    </div>
    <div id="delete" style="display: none;">
        <div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title">fourth Card Content</h2>
                <p></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<div class="logo">
    <img src="assets/logo.png">
</div>
<script>
    function toggleCard(cardId) {
        var card = document.getElementById(cardId);

        if (card.style.display === "none" || card.style.display === "") {
            document.getElementById('select').style.display = "none";
            document.getElementById('add').style.display = "none";
            document.getElementById('delete').style.display = "none";
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    }
</script>

</body>
</html>