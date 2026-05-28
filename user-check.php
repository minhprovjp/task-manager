<?php
    include('config/constants.php');
?>
<html>
    <head>
        <title>Task Manager - User Lookup</title>
        <link rel="stylesheet" href="<?php echo SITEURL; ?>css/style.css" />
    </head>
    <body>
        <div class="wrapper">
            <h1>TASK MANAGER</h1>
            <div class="menu">
                <a href="<?php echo SITEURL; ?>">Home</a>
                <a href="<?php echo SITEURL; ?>manage-list.php">Manage Lists</a>
                <a href="<?php echo SITEURL; ?>search.php">Search Tasks</a>
                <a href="<?php echo SITEURL; ?>user-check.php">User Lookup</a>
                <a href="<?php echo SITEURL; ?>profile.php">User Profile</a>
                <a href="<?php echo SITEURL; ?>contact.php">Contact Us</a>
            </div>
            <h3>User Lookup</h3>

            <form method="GET" action="">
                <table class="tbl-half">
                    <tr>
                        <td>User ID:</td>
                        <td><input type="text" name="id" placeholder="Enter user ID" style="width:100%;padding:8px;" /></td>
                        <td><input class="btn-primary" type="submit" value="Check" /></td>
                    </tr>
                </table>
            </form>

            <p>
            <?php
                if (isset($_GET['id']))
                {
                    $user_id = $_GET['id'];

                    $conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD) or die(mysqli_error());
                    $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());

                    $sql = "SELECT * FROM tbl_users WHERE user_id = $user_id";
                    $res = mysqli_query($conn, $sql);

                    if ($res && mysqli_num_rows($res) > 0)
                    {
                        echo "User found.";
                    }
                    else
                    {
                        echo "User not found.";
                    }
                }
            ?>
            </p>
        </div>
    </body>
</html>
