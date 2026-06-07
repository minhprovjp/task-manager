<?php
    include('config/constants.php');

    $error = '';
    if (isset($_POST['submit'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'];

        $conn = mysqli_connect(LOCALHOST, DB_USER_AUTH, DB_PASS_AUTH) or die(mysqli_error());
        $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());

        $check_stmt = mysqli_prepare($conn, "SELECT * FROM tbl_users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Username or Email already exists!";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO tbl_users (username, password, email) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $username, $password, $email);
            $res = mysqli_stmt_execute($stmt);

            if ($res == true) {
                $_SESSION['user'] = $username;
                $_SESSION['user_id'] = mysqli_insert_id($conn);
                header('location:'.SITEURL);
            } else {
                $error = "Failed to add user!";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($check_stmt);
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
                            <input class="btn-primary btn-lg" type="submit" name="submit" value="Sign Up" style="margin-top: 10px; padding: 10px 20px;" />
                            &nbsp;&nbsp;
                            <a href="<?php echo SITEURL; ?>login.php" class="btn-primary btn-lg" style="margin-top: 10px; padding: 10px 20px; text-decoration: none; display: inline-block;">Back to Login</a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>
