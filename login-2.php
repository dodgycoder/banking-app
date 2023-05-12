<?php


    function logger (&$id,&$error) {
    
        $url = '<loggerurl>';
        $data = array('id' => $id, 'content' => $error);

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => "Log - ".$id." Message : ".$error
            )
        );

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
    
    }
   
    $azureServer = '<databaseserver>.database.windows.net';
    $azureDatabase = 'userdb';
    $connectionInfo = array('Database'=>$azureDatabase,'Authentication'=>'ActiveDirectoryMsi');
    $conn = sqlsrv_connect($azureServer, $connectionInfo);
    if ($conn === false) {
        echo "Could not connect with Authentication=ActiveDirectoryMsi (system-assigned).\n";
        print_r(sqlsrv_errors());
    } else {
        
        $tsql = "select * from [dbo].[users] where username='".$_POST['username']."'";
        $stmt = sqlsrv_query($conn, $tsql);
        
        if ($stmt === false) {
            //Redirect to login page
            header('Location: index.php');
            $error="No user found"; 
            logger($_POST['username'],$error);
        }
        else{
            
            $count = 0;
            while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
                $count++;   
                if (password_verify($_POST['password'],$row['password'])) {
                    // Passwords match, log in the user
                    session_start();
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['name'] = $row['name'];
                    $error="User authenticated ok";
                    logger($_POST['username'],$error);
                    http_response_code(200);
                    
                    header('Location: upload.php');
                } else{
                   $error="Incorrect Password Entered by the user"; 
                   logger($_POST['username'],$error);
                   http_response_code(404);
                   echo "Incorrect user and password entered";
                }    
            }
            if($count == 0)
            {
                
                $error="No user found"; 
                logger($_POST['username'],$error);
                http_response_code(404);
                echo "Incorrect user name password";

            }    
            
        }
        
        sqlsrv_close($conn);
    }

    

?>    