<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<style>
    * {
        margin: 0;
        padding: 0;
    }

    *::selection {
        background-color: #ecffa1;
    }

    .header {
        text-align: center;
        padding: 20px;
    }
</style>

<?php
function connectToOracle() {
    $conn = OCILogon("ora_sidnand", "a76648070", "dbhost.students.cs.ubc.ca:1522/stu");
    if (!$conn) {
        $err = OCIError();
        $error_message = "Oracle Connect Error " . $err['message'];
        echo "<script>";
        echo "console.log('$error_message');";
        echo "</script>";
        return false;
    }

    echo "<script>";
    echo "console.log('Connected to Oracle!');";
    echo "</script>";
    return $conn;
}

function closeOracleConnection($conn) {
    OCILogoff($conn);
}

function executeOracleQuery($conn, $sql) {
    $stmt = OCIParse($conn, $sql);
    if (!$stmt) {
        $err = OCIError($conn);
        echo "Oracle Parse Error " . $err['message'];
        return false;
    }
    
    $result = OCIExecute($stmt);
    if (!$result) {
        $err = OCIError($stmt);
        echo "Oracle Execute Error " . $err['message'];
        return false;
    }

    return $stmt;
}
?>