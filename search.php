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
            </div>
            <h3>Search Tasks</h3>

            <form method="GET" action="">
                <table class="tbl-half">
                    <tr>
                        <td><input type="text" name="q" placeholder="Search tasks..." value="<?php echo htmlspecialchars($search); ?>" style="width:100%;padding:8px;" /></td>
                        <td><input class="btn-primary btn-lg" type="submit" value="Search" /></td>
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
                    $conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD) or die(mysqli_error());
                    $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());

                    // Use subquery to hide Flag 1 (list_id 999)
                    $sql = "SELECT * FROM (SELECT * FROM tbl_tasks WHERE list_id != 999) AS t WHERE task_name LIKE '%$search%' OR task_description LIKE '%$search%'";
                    $res = mysqli_query($conn, $sql);

                    if ($res && mysqli_num_rows($res) > 0)
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
                ?>
            </table>
            <?php endif; ?>
        </div>
    </body>
</html>
