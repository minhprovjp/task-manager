<?php
    include('config/constants.php');

    $error = '';
    if (isset($_POST['submit'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'];

        $conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD) or die(mysqli_error());
        $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());

        $sql = "INSERT INTO tbl_users (username, password, email) VALUES ('$username', '$password', '$email')";
        $res = mysqli_query($conn, $sql);

        if ($res == true) {
            $_SESSION['user'] = $username;
            header('location:'.SITEURL);
        } else {
            $error = "Failed to add user!";
        }
    }
?>
<html>
    <head>
        <title>Task Manager - Sign Up</title>
        <link rel="stylesheet" href="<?php echo SITEURL; ?>css/style.css" />
    </head>
    <body>
        <div class="wrapper">
            <h1>TASK MANAGER</h1>
            <h3>Sign Up</h3>
            <p style="color:red;"><?php echo $error; ?></p>
            <form method="POST" action="">
                <table class="tbl-half">
                    <tr>
                        <td>Username:</td>
                        <td><input type="text" name="username" placeholder="Enter Username" required /></td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><input type="password" name="password" placeholder="Enter Password" required /></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td><input type="email" name="email" placeholder="Enter Email" required /></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input class="btn-primary btn-lg" type="submit" name="submit" value="Sign Up" />
                        </td>
                    </tr>
                </table>
            </form>
            <br>
            <a href="<?php echo SITEURL; ?>login.php">Back to Login</a>
        </div>
    </body>
</html>
