<?php
   
    $azureServer = 'app-auth-db.database.windows.net';
    $azureDatabase = 'userdb';
    $connectionInfo = array('Database'=>$azureDatabase,'Authentication'=>'ActiveDirectoryMsi');
    $conn = sqlsrv_connect($azureServer, $connectionInfo);
    if ($conn === false) {
        echo "Could not connect with Authentication=ActiveDirectoryMsi (system-assigned).\n";
        print_r(sqlsrv_errors());
    } else {
        
        $tsql = "select * from [dbo].[users] where username='".$_POST['username']."'";
        $stmt = sqlsrv_query($conn, $tsql);
        var_dump($stmt);
        if ($stmt === false) {
            //Redirect to login page
            header('Location: index.php');
        }
        else{
               
            while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
                echo $row['password'];
                if (password_verify($row['password'], $_POST['password'])) {
                    // Passwords match, log in the user
                    session_start();
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['name'] = $row['name'];
                    header('Location: upload.php');
                } else{
                   echo "Incorrect Username/Password"; 
                }    
            }    
            
        }
        
        sqlsrv_close($conn);
    }

    

?>    