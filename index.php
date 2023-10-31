<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Service</title>
</head>
<body>
    <?php
        include('base.php');
        $conn = connectToOracle();
    ?>


    <div class="container">
        <h1 class="header">Email Service</h1>

        <div class="row">
            <div class="col">

                <form action="" class="form-group">
                    <h3>Login</h3>
                    <br>

                    <div class="row">
                        <div class="col">
                            <input type="phone-number" name="phone-number" class="form-control" id="phone-number" placeholder="Phone number" required>
                        </div>
                    </div>

                    <br>

                    <button type="submit" class="btn btn-primary">Login</button>
                </form>

            </div>

            <div class="col">
                <form action="" class="form-group">
                    <h3>Signup</h3>
                    <br>

                    <div class="row">
                        <div class="col">
                            <input type="firstname" name="firstname" class="form-control" id="firstname" placeholder="Firstname" required>
                        </div>
                        <div class="col">
                            <input type="lastname" name="lastname" class="form-control" id="lastname" placeholder="Lastname" required>
                        </div>
                    </div>

                    <br>

                    <div class="row">
                        <div class="col">
                            <input type="phone-number" name="phone-number" class="form-control" id="phone-number" placeholder="Phone number" required>
                        </div>
                    </div>

                    <br>

                    <button type="submit" class="btn btn-primary">Signup</button>
                </form>


            </div>
        </div>

    </div>
</body>
</html>