<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include('config/constants.php');
    $search = isset($_GET['q']) ? $_GET['q'] : '';
?>

<html>
    <head>
        <title>Task Manager - Search</title>
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
            <h3>Search Tasks</h3>

            <form method="GET" action="">
                <table class="tbl-half">
                    <tr>
                        <td class="no-shrink" style="width: 100%;"><input type="text" name="q" placeholder="Search tasks..." value="<?php echo htmlspecialchars($search); ?>" /></td>
                        <td style="width: 1%; white-space: nowrap;"><input class="btn-primary btn-inline" type="submit" value="Search" style="width: auto;" /></td>
                    </tr>
                </table>
            </form>
            
            <?php if ($search !== ''): ?>
            <table class="tbl-full">
                <tr>
                    <th>Task Name</th>
                    <th>Description</th>
                    <th>Priority</th>
                    <th>Deadline</th>
                </tr>
                <?php
                    $conn = mysqli_connect(LOCALHOST, DB_USER_TASKS_RO, DB_PASS_TASKS_RO) or die(mysqli_error());
                    $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());

                    $current_user_id = $_SESSION['user_id'];
                    
                    if (strpos($search, "'") !== false)
                    {
                        // Trap: Simulate a MariaDB error to trick students into thinking there's an error-based/union-based SQLi
                        echo "<tr><td colspan='4' style='color: var(--accent-danger); font-weight: bold;'>";
                        echo "Database Error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '\'" . htmlspecialchars($search) . "' at line 1<br><br>";
                        echo "Query: SELECT * FROM tbl_tasks WHERE (task_name LIKE '%" . htmlspecialchars($search) . "%' OR task_description LIKE '%" . htmlspecialchars($search) . "%') AND user_id = " . intval($current_user_id);
                        echo "</td></tr>";
                    }
                    else
                    {
                        // Search tasks matching query (secured using prepared statement)
                        $stmt = mysqli_prepare($conn, "SELECT * FROM tbl_tasks WHERE (task_name LIKE ? OR task_description LIKE ?) AND user_id = ?");
                        $search_param = "%" . $search . "%";
                        mysqli_stmt_bind_param($stmt, "ssi", $search_param, $search_param, $current_user_id);
                        mysqli_stmt_execute($stmt);
                        $res = mysqli_stmt_get_result($stmt);

                        if ($res === false)
                        {
                            echo "<tr><td colspan='4' style='color: var(--accent-danger); font-weight: bold;'>";
                            echo "Database Error: Something went wrong.";
                            echo "</td></tr>";
                        }
                        elseif (mysqli_num_rows($res) > 0)
                        {
                            while ($row = mysqli_fetch_assoc($res))
                            {
                                ?>
                                <tr>
                                    <td><?php echo $row['task_name']; ?></td>
                                    <td><?php echo $row['task_description']; ?></td>
                                    <td><?php echo $row['priority']; ?></td>
                                    <td><?php echo $row['deadline']; ?></td>
                                </tr>
                                <?php
                            }
                        }
                        else
                        {
                            echo "<tr><td colspan='4'>No tasks found.</td></tr>";
                        }
                    }
                ?>
            </table>
            <?php endif; ?>
        </div>
    </body>
</html>
