<?php

session_start();
  if (isset($_SESSION['user_id'])) {
      header('Location: upload.php');
      exit;
    }
?>


<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <style>
    /* Center the form */
    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    /* Center the content in the form */
    .form-group {
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="col-sm-6">
      <a target="_blank" href="https://www.zscaler.com">
                <img class="rounded mx-auto d-block" src='logo.png' style="width:200px;height:200px;">
      </a>
      <h2>Login</h2>
      <!--<form action="login-2.php" method="POST">!-->
      <form id="login-form">  
        <div class="form-group">
          
          <input type="text" class="form-control" id="username" placeholder="Enter username" name="username">
        </div>
        <div class="form-group">
          
          <input type="password" class="form-control" id="password" placeholder="Enter password" name="password">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
        <p><div id="response-message" class="text-primary"></div></p>
      </form>
    </div>
  </div>
  <script>
   
    $(function() {
      $('#login-form').submit(function(event) {
        // Prevent the form from submitting normally
        event.preventDefault();

        // Get the form data
        var formData = $(this).serialize();

        // Send the form data using AJAX
        $.ajax({
          url: 'login-2.php',
          type: 'POST',
          data: formData,
          success: function(response) {
            // Redirect to the upload page on success            
            window.location.reload();
          },
          error: function(xhr, status, error) {
            // Show an error message
            $('#response-message').html(xhr.responseText);
          }
        });
      });
    });
  </script>


</body>
</html>

