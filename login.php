<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include('config/constants.php');

    $error = '';
    if (isset($_POST['submit'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD) or die(mysqli_error());
        $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());

        // Vulnerable Query
        $sql = "SELECT * FROM tbl_users WHERE username = '$username' AND password = '$password'";
        $res = mysqli_query($conn, $sql);

        if ($res == true) {
            $count = mysqli_num_rows($res);
            if ($count > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($row['username'] === 'admin') {
                    $_SESSION['user'] = $row['username'];
                    $error = "Success! Flag: DBS401{byp4ss_auth_w1th_sql1} <br><br><a class='btn-primary' href='".SITEURL."'>Go to Dashboard</a>";
                } else {
                    $_SESSION['user'] = $row['username'];
                    $error = "Logged in as " . $row['username'] . ". But no flag for you! <br><br><a class='btn-primary' href='".SITEURL."'>Go to Dashboard</a>";
                }
            } else {
                $error = "Invalid username or password!";
            }
        }
    }
?>

<html>
    <head>
        <title>Task Manager - Login</title>
        <link rel="stylesheet" href="<?php echo SITEURL; ?>css/style.css" />
    </head>
    <body>
        <div class="wrapper">
            <h1>TASK MANAGER</h1>
            <a class="btn-secondary" href="<?php echo SITEURL; ?>">Home</a>
            <h3>Login</h3>

            <p style="color:red;"><?php echo $error; ?></p>

            <form method="POST" action="">
                <table class="tbl-half">
                    <tr>
                        <td>Username:</td>
                        <td><input type="text" name="username" placeholder="Enter Username" /></td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><input type="password" name="password" placeholder="Enter Password" /></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input class="btn-primary btn-lg" type="submit" name="submit" value="Login" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>
