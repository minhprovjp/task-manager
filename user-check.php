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
                <a href="<?php echo SITEURL; ?>logout.php">Logout</a>
            </div>
            <h3>User Lookup</h3>

            <form method="GET" action="">
                <table class="tbl-half">
                    <tr>
                        <td>User ID:</td>
                        <td style="width: 100%;"><input type="text" name="id" placeholder="Enter user ID" /></td>
                        <td style="width: 1%; white-space: nowrap;"><input class="btn-primary btn-inline" type="submit" value="Check" style="width: auto;" /></td>
                    </tr>
                </table>
            </form>

            <p>
            <?php
                if (isset($_GET['id']))
                {
                    $user_id = $_GET['id'];

                    $conn = mysqli_connect(LOCALHOST, DB_USER_LOOKUP, DB_PASS_LOOKUP) or die(mysqli_error());
                    $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());

                    // Normalize input to strip comments and multiple spaces for honeypot processing
                    $normalized_id = preg_replace('/\/\*.*?\*\//', ' ', $user_id);
                    $normalized_id = preg_replace('/--.*/', '', $normalized_id);
                    $normalized_id = preg_replace('/#.*/', '', $normalized_id);
                    $normalized_id = preg_replace('/\s+/', ' ', trim($normalized_id));

                    $is_trap = false;
                    $trap_result = false;

                    // Match Boolean injection test patterns, e.g. "AND X=Y" or "OR X=Y"
                    if (preg_match('/\b(and|or)\b\s+(\d+)\s*(=|!=|<>|>|<)\s*(\d+)/i', $normalized_id, $matches)) {
                        $is_trap = true;
                        $operator = strtolower($matches[1]);
                        $val1 = intval($matches[2]);
                        $comparison = $matches[3];
                        $val2 = intval($matches[4]);

                        $expr_result = false;
                        if ($comparison === '=') {
                            $expr_result = ($val1 === $val2);
                        } elseif ($comparison === '!=' || $comparison === '<>') {
                            $expr_result = ($val1 !== $val2);
                        } elseif ($comparison === '>') {
                            $expr_result = ($val1 > $val2);
                        } elseif ($comparison === '<') {
                            $expr_result = ($val1 < $val2);
                        }

                        if ($operator === 'and') {
                            $trap_result = $expr_result;
                        } else {
                            $trap_result = true; // base_query OR true is always true
                        }
                    }

                    if ($is_trap) {
                        if ($trap_result) {
                            echo "User found.";
                        } else {
                            echo "User not found.";
                        }
                    } else {
                        // Secure Prepared Statement execution
                        $stmt = mysqli_prepare($conn, "SELECT user_id, username, role FROM tbl_users WHERE user_id = ?");
                        mysqli_stmt_bind_param($stmt, "i", $user_id);
                        mysqli_stmt_execute($stmt);
                        $res = mysqli_stmt_get_result($stmt);

                        if ($res && mysqli_num_rows($res) > 0)
                        {
                            echo "User found.";
                        }
                        else
                        {
                            echo "User not found.";
                        }
                        mysqli_stmt_close($stmt);
                    }
                }
            ?>
            </p>
        </div>
    </body>
</html>
