<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPSC 304 Project</title>
</head>
<body>
    <?php
        if ($c=OCILogon("ora_sidnand", "a76648070", "dbhost.students.cs.ubc.ca:1522/stu")) {
            echo "Successfully connected to Oracle.\n";
            OCILogoff($c);
        } else {
            $err = OCIError();
            echo "Oracle Connect Error " . $err['message'];
        }
    ?>


    <p>Hello, world!</p>
</body>
</html>