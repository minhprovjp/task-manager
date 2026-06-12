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
                    
                    if (strpos($search, "'") !== false || preg_match('/(updatexml|extractvalue)\s*\(/i', $search))
                    {
                        // Normalize search input to resolve comments for honeypot matching
                        $normalized_search = preg_replace('/\/\*.*?\*\//', ' ', $search);
                        $normalized_search = preg_replace('/\s+/', ' ', trim($normalized_search));

                        // Check if it is trying to use updatexml or extractvalue error-based SQLi
                        if (preg_match('/(updatexml|extractvalue)\s*\(/i', $normalized_search)) {
                            // Helper function to simulate subqueries
                            if (!function_exists('simulate_subquery')) {
                                function simulate_subquery($query_str) {
                                    $query_str = strtolower(trim($query_str, '() '));
                                    
                                    // 1. Check for schema_name / databases
                                    if (strpos($query_str, 'schema_name') !== false) {
                                        return 'B0^._PC';
                                    }
                                    
                                    // 2. Check for table_name / tables
                                    if (strpos($query_str, 'table_name') !== false) {
                                        return 'tbl_tasks,tbl_npcs,tbl_lists,vw_haha_flag,vw_mixi_flag';
                                    }
                                    
                                    // 3. Check for column_name / columns
                                    if (strpos($query_str, 'column_name') !== false) {
                                        return 'user_id,username,password,role,token, kh0_g@_d3_t3m';
                                    }
                                    
                                    // 4. Check for vw_error_flag
                                    if (strpos($query_str, 'vw_error_flag') !== false) {
                                        return 'Nhom4-Flag2{f4k3d_3rr0r}';
                                    }
                                    
                                    // 5. Check for vw_time_flag
                                    if (strpos($query_str, 'vw_time_flag') !== false) {
                                        return 'Nhom4-Flag3{th1s_15_4_f4k3_t1m3_fl4g}';
                                    }
                                    
                                    // 6. Check for general database(), version(), user()
                                    if (strpos($query_str, 'database()') !== false) {
                                        return '120_y3n_L4ng~';
                                    }
                                    if (strpos($query_str, 'version()') !== false || strpos($query_str, '@@version') !== false) {
                                        return '18.36.64-OiiDB';
                                    }
                                    if (strpos($query_str, 'user()') !== false) {
                                        return '4dm1n';
                                    }
                                    if (strpos($query_str, 'token') !== false) {
                                        return 'B4~_m1a';
                                    }
                                    
                                    // Default fallback
                                    return '1';
                                }
                            }

                            $error_val = '';
                            
                            $start_pos = stripos($normalized_search, 'concat(');
                            if ($start_pos !== false) {
                                $start_pos += 7; // Length of 'concat('
                                $depth = 1;
                                $concat_content = '';
                                $len = strlen($normalized_search);
                                for ($i = $start_pos; $i < $len; $i++) {
                                    $char = $normalized_search[$i];
                                    if ($char === '(') {
                                        $depth++;
                                    } elseif ($char === ')') {
                                        $depth--;
                                    }
                                    if ($depth === 0) {
                                        break;
                                    }
                                    $concat_content .= $char;
                                }
                                
                                $args = preg_split('/,(?![^(]*\))/', $concat_content);
                                foreach ($args as $arg) {
                                    $arg = trim($arg);
                                    if (preg_match('/^0x([0-9a-fA-F]+)$/', $arg, $hex_match)) {
                                        $error_val .= hex2bin($hex_match[1]);
                                    } elseif (preg_match('/^\'(.*)\'$/s', $arg, $str_match)) {
                                        $error_val .= $str_match[1];
                                    } elseif (preg_match('/^"(.*)"$/s', $arg, $str_match)) {
                                        $error_val .= $str_match[1];
                                    } elseif (stripos($arg, 'database()') !== false) {
                                        $error_val .= 'B0^._PC';
                                    } elseif (stripos($arg, 'version()') !== false || stripos($arg, '@@version') !== false) {
                                        $error_val .= '18.36.64-OiiDB';
                                    } elseif (stripos($arg, 'user()') !== false) {
                                        $error_val .= '4dm1n';
                                    } elseif (preg_match('/^\((SELECT.*)\)$/is', $arg, $sub_match)) {
                                        $error_val .= simulate_subquery($sub_match[1]);
                                    } else {
                                        $clean_arg = trim($arg, '() ');
                                        if (is_numeric($clean_arg)) {
                                            $error_val .= $clean_arg;
                                        } else {
                                            $error_val .= '1';
                                        }
                                    }
                                }
                            } else {
                                if (preg_match('/0x([0-9a-fA-F]+)/', $normalized_search, $hex_match)) {
                                    $error_val = hex2bin($hex_match[1]);
                                } else {
                                    $error_val = '1';
                                }
                            }

                            echo "<tr><td colspan='4' style='color: var(--accent-danger); font-weight: bold;'>";
                            echo "Database Error: XPATH syntax error: '" . htmlspecialchars($error_val) . "'";
                            echo "</td></tr>";
                        } else {
                            // Trap: Simulate a MariaDB error to trick students into thinking there's an error-based/union-based SQLi
                            echo "<tr><td colspan='4' style='color: var(--accent-danger); font-weight: bold;'>";
                            echo "Database Error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '\'" . htmlspecialchars($search) . "' at line 1<br><br>";
                            echo "Query: SELECT * FROM tbl_tasks WHERE (task_name LIKE '%" . htmlspecialchars($search) . "%' OR task_description LIKE '%" . htmlspecialchars($search) . "%') AND user_id = " . intval($current_user_id);
                            echo "</td></tr>";
                        }
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
