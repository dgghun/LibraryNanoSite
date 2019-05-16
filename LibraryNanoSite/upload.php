<?php
// Include the database configuration file
session_start();
require_once 'functions.php';
$statusMsg = '';

// File upload path
$targetDir = "uploads/";
$fileName = basename($_FILES["file"]["name"]);
$targetFilePath = $targetDir . $fileName;
$fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);

echo "<pre>" . print_r($_POST) . "</pre>";
echo "<pre>" . print_r($_FILES). "</pre>";
echo "<pre>" . print_r($_SESSION). "</pre>";
echo "File Type: ".$fileType;

if(isset($_POST["upload"]) && !empty($_FILES["file"]["name"])){
    
    $allowTypes = array('jpg','png','jpeg','gif','pdf');  // Allow certain file formats

    if(in_array($fileType, $allowTypes)){

       // Upload file to server
      if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){
        // Insert image file name into database
            $statusMsg = "The file ".$fileName. " has been uploaded successfully.";
       }
       else{
           $statusMsg = "Sorry, there was an error uploading your file.";
       }
    }
    else{
        $statusMsg = 'Sorry, only JPG, JPEG, PNG, GIF, & PDF files are allowed to upload.';
    }
}
else{
    $statusMsg = 'Please select a file to upload.';
}

// Display status message
echo $statusMsg;
?>