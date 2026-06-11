<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include('config/constants.php');

    $msg = '';
    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $message = $_POST['message'];

        // Trap: Simulate a time-based blind SQL Injection vulnerability.
        // If they use sleep() or benchmark() style payloads, trigger a PHP sleep to mimic it safely without databases being impacted.
        $trap_payload = $name . ' ' . $email . ' ' . $message;
        if (preg_match('/(?:pg_)?sleep\s*\(\s*(\d+)\s*\)/i', $trap_payload, $matches)) {
            $seconds = intval($matches[1]);
            if ($seconds > 0) {
                sleep(min($seconds, 15));
            }
        } elseif (preg_match('/benchmark\s*\(/i', $trap_payload)) {
            sleep(5);
        }

        $conn = mysqli_connect(LOCALHOST, DB_USER_FEEDBACK, DB_PASS_FEEDBACK) or die(mysqli_error());
        $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());

        // Secure Prepared Statement
        $stmt = mysqli_prepare($conn, "INSERT INTO tbl_feedback (name, email, message) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $message);
        
        $res = mysqli_stmt_execute($stmt);

        // Safe Error Handling (No raw DB errors exposed)
        if ($res == true) {
            $msg = "Thank you for your feedback!";
        } else {
            $msg = "Error submitting feedback. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }
?>

<html>
    <head>
        <title>Task Manager - Contact Us</title>
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
            <h3>Contact Us / Feedback</h3>

            <p style="color:green;"><?php echo $msg; ?></p>

            <form method="POST" action="">
                <table class="tbl-half">
                    <tr>
                        <td>Name:</td>
                        <td><input type="text" name="name" placeholder="Your Name" required /></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td><input type="email" name="email" placeholder="Your Email" required /></td>
                    </tr>
                    <tr>
                        <td>Message:</td>
                        <td><textarea name="message" placeholder="Your Message" required></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input class="btn-primary btn-lg" type="submit" name="submit" value="Send Feedback" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>
