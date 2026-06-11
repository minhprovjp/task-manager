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
        //Write the Query to DELETE List from DAtabase
        $sql = "DELETE FROM tbl_lists WHERE list_id=$list_id AND user_id=$current_user_id";
        
        //Execute The Query
        $res = mysqli_query($conn, $sql);
        
        //Check whether the query executed successfully or not
        if($res==true)
        {
            // Also delete associated tasks
            mysqli_query($conn, "DELETE FROM tbl_tasks WHERE list_id=$list_id AND user_id=$current_user_id");
            
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