<?php 

    include('config/constants.php');
    
    //Check task_id in URL
    if(isset($_GET['task_id']))
    {
        //Delete the Task from Database
        //Get the Task ID
        $task_id = $_GET['task_id'];
        
        //Connect Databaes
        $conn = mysqli_connect(LOCALHOST, DB_USER_TASKS_RW, DB_PASS_TASKS_RW) or die(mysqli_error());
        
        //SElect Database
        $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());
        
        $current_user_id = $_SESSION['user_id'];
        //SQL Query to DELETE TASK
        $sql = "DELETE FROM tbl_tasks WHERE task_id=$task_id AND user_id=$current_user_id";
        
        //Execute Query
        $res = mysqli_query($conn, $sql);
        
        //CHeck if the Query Executed Successfully or Not
        if($res==true)
        {
            //Query Executed Successfully and TAsk Deleted
            $_SESSION['delete'] = "Task Deleted Successfully.";
            
            //redirect to Homepage
            header('location:'.SITEURL);
        }
        else
        {
            //FAiled to Delete Task
            $_SESSION['delete_fail'] = "Failed to Delete Task";
            
            //Redirect to Home PAge
            header('location:'.SITEURL);
        }
        
    }
    else
    {
        //Redirect to Home
        header('location:'.SITEURL);
    }

?>