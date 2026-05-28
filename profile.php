<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include('config/constants.php');

    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
?>

<html>
    <head>
        <title>Task Manager - Profile</title>
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
            <h3>User Profile</h3>

            <form method="GET" action="">
                <table class="tbl-half">
                    <tr>
                        <td>Enter User ID (e.g. 1):</td>
                        <td><input type="text" name="user_id" placeholder="User ID" value="<?php echo htmlspecialchars($user_id); ?>" style="width:100%;padding:8px;" /></td>
                        <td><input class="btn-primary btn-lg" type="submit" value="View" style="padding: 10px 40px; min-width: 120px; font-size: 16px;" /></td>
                    </tr>
                </table>
            </form>

            <?php if ($user_id !== ''): ?>
            <table class="tbl-full">
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
                <?php
                    $conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD) or die(mysqli_error());
                    $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());

                    // Vulnerable Query
                    $sql = "SELECT username, email, role FROM tbl_users WHERE user_id = $user_id";
                    
                    // Add this line to make errors visible which makes boolean-based or error-based possible if union doesn't work out
                    $res = mysqli_query($conn, $sql) or die(mysqli_error($conn));

                    if ($res && mysqli_num_rows($res) > 0)
                    {
                        while ($row = mysqli_fetch_assoc($res))
                        {
                            ?>
                            <tr>
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['role']; ?></td>
                            </tr>
                            <?php
                        }
                    }
                    else
                    {
                        echo "<tr><td colspan='3'>User not found.</td></tr>";
                    }
                ?>
            </table>
            <?php endif; ?>
        </div>
    </body>
</html>
