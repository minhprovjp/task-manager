<?php 

    include('config/constants.php'); 
    
    
    //Get the Current Values of Selected List
    if(isset($_GET['list_id']))
    {
        //Get the List ID value
        $list_id = $_GET['list_id'];
        
        //Connect to Database
        $conn = mysqli_connect(LOCALHOST, DB_USER_TASKS_RW, DB_PASS_TASKS_RW) or die(mysqli_error());
        
        //SElect DAtabase
        $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());
        
        $current_user_id = $_SESSION['user_id'];
        //Query to Get the Values from Database using prepared statement
        $stmt = mysqli_prepare($conn, "SELECT * FROM tbl_lists WHERE list_id=? AND user_id=?");
        mysqli_stmt_bind_param($stmt, "ii", $list_id, $current_user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        
        //CHekc whether the query executed successfully or not
        if($res==true)
        {
            //Get the Value from Database
            $row = mysqli_fetch_assoc($res); //Value is in array
            
            if ($row) {
                //Create Individual Variable to save the data
                $list_name = $row['list_name'];
                $list_description = $row['list_description'];
            } else {
                header('location:'.SITEURL.'manage-list.php');
                exit;
            }
        }
        else
        {
            //Go Back to Manage List Page
            header('location:'.SITEURL.'manage-list.php');
        }
    }

?>




<html>

    <head>
        <title>Task Manager with PHP and MySQL</title>
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
        
        <a class="btn-secondary" href="<?php echo SITEURL; ?>manage-list.php">Manage Lists</a>
            
       
        
        
        <h3>Update List Page</h3>
        
        <p>
            <?php 
                //Check whether the session is set or not
                if(isset($_SESSION['update_fail']))
                {
                    echo $_SESSION['update_fail'];
                    unset($_SESSION['update_fail']);
                }
            ?>
        </p>
        
        <form method="POST" action="">
        
            <table class="tbl-half">
                <tr>
                    <td>List Name: </td>
                    <td><input type="text" name="list_name" value="<?php echo $list_name; ?>" required="required" /></td>
                </tr>
                
                <tr>
                    <td>List Description: </td>
                    <td>
                        <textarea name="list_description">
                            <?php echo $list_description; ?>
                        </textarea>
                    </td>
                </tr>
                
                <tr>
                    <td><input class="btn-lg btn-primary" type="submit" name="submit" value="UPDATE" /></td>
                </tr>
            </table>
            
        </form>
        
        </div>
        
    
    </body>

</html>


<?php 

    //Check whether the Update is Clicked or Not
    if(isset($_POST['submit']))
    {
        //echo "Button Clicked";
        
        //Get the Updated Values from our Form
        $list_name = $_POST['list_name'];
        $list_description = $_POST['list_description'];
        
        //Connect Database
        $conn2 = mysqli_connect(LOCALHOST, DB_USER_TASKS_RW, DB_PASS_TASKS_RW) or die(mysqli_error());
        
        //SElect the Database
        $db_select2 = mysqli_select_db($conn2, DB_NAME);
        
        $current_user_id = $_SESSION['user_id'];
        //QUERY to Update List using prepared statement
        $stmt2 = mysqli_prepare($conn2, "UPDATE tbl_lists SET list_name=?, list_description=? WHERE list_id=? AND user_id=?");
        mysqli_stmt_bind_param($stmt2, "ssii", $list_name, $list_description, $list_id, $current_user_id);
        $res2 = mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
        
        //Check whether the query executed successfully or not
        if($res2==true)
        {
            //Update Successful
            //SEt the Message
            $_SESSION['update'] = "List Updated Successfully";
            
            //Redirect to Manage List PAge
            header('location:'.SITEURL.'manage-list.php');
        }
        else
        {
            //FAiled to Update
            //SEt Session Message
            $_SESSION['update_fail'] = "Failed to Update List";
            //Redirect to the Update List PAge
            header('location:'.SITEURL.'update-list.php?list_id='.$list_id);
        }
        
    }
?>









































