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
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    session_start();

    function connect_to_oracle() {
        $conn = oci_connect("ora_sidnand", "a76648070", "dbhost.students.cs.ubc.ca:1522/stu");
        if (!$conn) {
            $err = oci_error();
            $error_message = "Unable to connect to database. Contact admin.";
            return false;
        }

        echo "<script>"
        . "console.log('Connected to Oracle!')"
        . "</script>";
        return $conn;
    }
?>