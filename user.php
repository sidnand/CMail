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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conn = connect_to_oracle();
        $username = $_POST["username"];
        $account = get_account($conn, $username);

        if ($account) {
            $error_message = "Username already exists. Please choose another.";
            oci_close($conn);
        } else {
            $result = insert_account($conn, $username);
            if (!$result) {
                $error_message = "Error creating user.";
                oci_close($conn);
            } else {
                header('Location: account.php');
            }
        }
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
                <h2 class="card-title">Select An Account</h2>
                <p></p>
            </div>
        </div>
    </div>
    <div id="add" style="display: none;">
        <div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <h2 class="card-title mb-3">Add An Account</h2>
                <?php
                    
                    if ($error_message != "") {
                        echo "<div class='alert alert-danger' role='alert'>"
                            . $error_message
                            . "</div>";
                    }
                
                ?>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <div class="row">
                        <div class="col">
                            <input type="username" name="username" class="form-control" id="username" placeholder="Username" required>
                        </div>
                        <div class="col">
                            <input type="submit" name="Create Account" class="btn btn-success" value="Create Account">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
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