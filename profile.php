<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include('config/constants.php');

    // Strip sleep keyword to make time-based blind SQL injection harder (nested keywords or alternative functions are required)
    $user_id = isset($_GET['user_id']) ? str_ireplace('sleep', '', $_GET['user_id']) : '';
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
                <a href="<?php echo SITEURL; ?>logout.php">Logout</a>
            </div>
            <h3>Your Profile</h3>
            
            <form method="GET" action="">
                <table class="tbl-half">
                    <tr>
                        <td>Enter User ID (e.g. 1):</td>
                        <td style="width: 100%;"><input type="text" name="user_id" placeholder="User ID" value="<?php echo htmlspecialchars($user_id); ?>" /></td>
                        <td style="width: 1%; white-space: nowrap;"><input class="btn-primary btn-inline" type="submit" value="View" style="width: auto;" /></td>
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
                    $conn = mysqli_connect(LOCALHOST, DB_USER_PROFILE, DB_PASS_PROFILE) or die(mysqli_error());
                    $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());

                    // Get the integer representation for the secure display query
                    $secure_user_id = intval($user_id);

                    // Safely query the requested user's profile to make the output static
                    $stmt = mysqli_prepare($conn, "SELECT username, email, role FROM tbl_users WHERE user_id = ?");
                    mysqli_stmt_bind_param($stmt, "i", $secure_user_id);
                    mysqli_stmt_execute($stmt);
                    $res_safe = mysqli_stmt_get_result($stmt);
                    $user_info = mysqli_fetch_assoc($res_safe);
                    mysqli_stmt_close($stmt);

                    // Vulnerable Query executed in background (suppress errors to prevent Error-Based SQLi)
                    $sql = "SELECT username, email, role FROM tbl_users WHERE user_id = $user_id";
                    $res = @mysqli_query($conn, $sql);

                    if ($user_info)
                    {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user_info['username']); ?></td>
                            <td><?php echo htmlspecialchars($user_info['email']); ?></td>
                            <td><?php echo htmlspecialchars($user_info['role']); ?></td>
                        </tr>
                        <?php
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
