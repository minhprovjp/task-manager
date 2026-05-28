<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include('config/constants.php');

    $msg = '';
    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $message = $_POST['message'];

        $conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD) or die(mysqli_error());
        $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());

        // Vulnerable Query
        $sql = "INSERT INTO tbl_feedback (name, email, message) VALUES ('$name', '$email', '$message')";
        
        $res = mysqli_query($conn, $sql);

        // We make the error visible so EXTRACTVALUE() error-based SQLi works
        if ($res == true) {
            $msg = "Thank you for your feedback!";
        } else {
            $msg = "Error submitting feedback: " . mysqli_error($conn);
        }
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
