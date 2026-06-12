<?php 
    //Include constants.php
    include('config/constants.php');
    
    //Check whether the list_id is assigned or not
    if(isset($_GET['list_id']))
    {
        //Delete the List from database
        //Get the list_id value from URL or Get Method
        $list_id = $_GET['list_id'];
        
        //Connect the DAtabase
        $conn = mysqli_connect(LOCALHOST, DB_USER_TASKS_RW, DB_PASS_TASKS_RW) or die(mysqli_error());
        
        //SElect Database
        $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());
        
        $current_user_id = $_SESSION['user_id'];
        //Write the Query to DELETE List from Database using prepared statement
        $stmt = mysqli_prepare($conn, "DELETE FROM tbl_lists WHERE list_id=? AND user_id=?");
        mysqli_stmt_bind_param($stmt, "ii", $list_id, $current_user_id);
        $res = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        //Check whether the query executed successfully or not
        if($res==true)
        {
            // Also delete associated tasks using prepared statement
            $stmt_tasks = mysqli_prepare($conn, "DELETE FROM tbl_tasks WHERE list_id=? AND user_id=?");
            mysqli_stmt_bind_param($stmt_tasks, "ii", $list_id, $current_user_id);
            mysqli_stmt_execute($stmt_tasks);
            mysqli_stmt_close($stmt_tasks);
            
            //Query Executed Successfully which means list is deleted successfully
            $_SESSION['delete'] = "List Deleted Successfully";
            
            //Redirect to Manage List Page
            header('location:'.SITEURL.'manage-list.php');
        }
        else
        {
            //Failed to Delete List
            $_SESSION['delete_fail'] = "Failed to Delete List.";
            header('location:'.SITEURL.'manage-list.php');
        }
    }
    else
    {
        //Redirect to Manage List Page
        header('location:'.SITEURL.'manage-list.php');
    }
?>