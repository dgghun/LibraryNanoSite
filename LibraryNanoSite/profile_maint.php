<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Style Sheets -->
  <link rel="stylesheet" href="_font-awesome_4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="_bootstrap_4.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="css/profile_maint.css">
  
	<!-- Scripts-->
	<script src="_popper_1.14.7/popper.min.js"></script>
	<script src="_jquery_3.3.1/jquery.min.js"></script>
	<script src="_bootstrap_4.3.1/js/bootstrap.min.js"></script>
	<script src="js/profile_maint.js"></script>
  
</head>

<?php
  require_once 'functions.php';
  session_start();
  
  // If last activity past 30 mins, destroy session and send to index
  $time = $_SERVER['REQUEST_TIME'];
  $timeout_duration = 1800; // 30 minute duration
  if (isset($_SESSION['timestamp']) && ($time - $_SESSION['timestamp']) > $timeout_duration) 
  {
    session_unset();
    session_destroy();
    session_start();
    $loggedin = FALSE;
    $_SESSION['timestamp'] = $time;
    echo "<script>".
                "var temp = confirm(\"Inactivity timeout.\\nPlease log in to use site.\");" .
                "window.location.assign('index.php');" .
         "</script>";
  }
  
  //Check if user has a session
  if (isset($_SESSION['user']))
  {
    $user     = $_SESSION['user'];
    $email    = $_SESSION['email'];
    $id       = $_SESSION['id'];
    $_SESSION['timestamp'] = $time;
    $userstr  = "$user's profile";
  }
  else {
    $loggedin = FALSE;
    echo "<script>".
                "var temp = confirm(\"Please log in to use site.\");" .
                "window.location.assign('index.php');" .
         "</script>";
  }
  
  //check for delete pub request
  if (isset($_POST['delete_pub'])){
    $pubId    = sanitizeString($_POST['pub_id']); 
    $title    = sanitizeString($_POST['title']); 
    $fileName = sanitizeString($_POST['file_name']);
    $filePath = sanitizeString($_POST['file_path']);
    $query = "DELETE FROM pubs WHERE id='$pubId'";
    
     echo "<script>".
            "var temp = confirm(\"Delete this item?\\nTitle: $title\\nFile: $fileName\");" .
            "if(temp) {deletePubMysql(\"$query\", \"$filePath\");}".
            "confirm('Deleted!');".
            "</script>";
            
     header("Refresh:0");
  }
  
  //check for upload request
  if (isset($_POST['upload']) && !empty($_FILES['file']['name'])){
    // File upload path
    $targetDir = "uploads/" . $id . "/";	//set upload directory to user id folder
    if(!is_dir($targetDir)) mkdir($targetDir); //test directory & create if not there
    $fileName = basename($_FILES["file"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);
    
    //get upload items
    $title    = sanitizeString($_POST['pub_title']);
    $author   = sanitizeString($_POST['pub_author']);
    $pubDate  = sanitizeString($_POST['pub_date']);
    $notes    = sanitizeString($_POST['pub_notes']);
    $category = sanitizeString($_POST['pub_category']);
    
    
    // Allow certain file formats
    $allowTypes = array('jpg','png','jpeg','gif','pdf','doc','docx','xls','xlsx','ppt','pptx');
    
    //check for duplicate file
    $result = queryMysql("SELECT * FROM pubs WHERE file_name='$fileName' AND user_id='$id'");
    
    $error = '';
    if($_FILES['file']['size'] > 15728640) { //15 MB (size is also in bytes)
      $error = "Sorry, file too large to upload.";
    }
    else if ($result->num_rows)
      $error = "$fileName is already in your library.";
    else if(in_array($fileType, $allowTypes)){  //check allowed file types
      
      // Upload file to server
      if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){

        // Insert image file name into database
        $result = insertPubMysql($id, $title, $notes, $author, $pubDate, $category, $targetFilePath, $fileName);
        
        if(!$result)  // if error while uploading to sql
          $error = 'File upload failed, please try again.';
      }
      else 
        $error = 'There was an error when uploading your file.';
    }
    else{
      $error = "The file type \'". $fileType / "\' is not allowed.";
    }
    
    //if error show it, else query change
    if ($error != ''){
      echo "<script>".
            "var temp = confirm(\"$error\\nFile not uploaded.\");" .
            "</script>";
    }
    else{
      echo "<script>".
            "var temp = confirm(\"File uploaded successfully!\");" .
            "</script>";
    }
  }
  
  //Check for edit password request
  if (isset($_POST['new_pass'])){
    $newPass = sanitizeString($_POST['new_pass']);
    $newPassRe = sanitizeString($_POST['new_pass_re']);
    
    //error check password
    $error = '';
    if(strlen($newPass) < 6)
      $error = 'Passwords must be at least 6 characters.';
    else if (!preg_match("/[a-z]/",$newPass) ||
             !preg_match("/[A-Z]/",$newPass) ||
             !preg_match("/[0-9]/",$newPass)
            )
      $error = 'Passwords require one each of a-z, A-Z and 0-9';
    else if ($newPass != $newPassRe)
      $error = 'Passwords do not match.';
    else{
      $newPass = hashPass($newPass); // hash the password
      $result = queryMysql("SELECT * FROM users WHERE password='$newPass' AND id='$id'");
      if ($result->num_rows)
        $error = "Same password entered.";
    }

    //if error show it, else query change
    if ($error != ''){
      echo "<script>".
            "var temp = confirm(\"$error\\nPassword not updated.\");" .
            "</script>";
    }
    else{
      $result = queryMysql("UPDATE users SET password = '$newPass' WHERE id = $id");
      $newPass = '';
      echo "<script>".
            "var temp = confirm(\"Password updated successfully!\");" .
            "</script>";
    }
  }
  
  //Check for edit email request
  if (isset($_POST['new_email'])){
    $newEmail = sanitizeString($_POST['new_email']);
    
    //error check email
    $result = queryMysql("SELECT * FROM users WHERE email='$newEmail'");
    $error = '';
    if ($newEmail == "")
      $error = 'Nothing entered.';
    else if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL))
      $error = "The Email address $newEmail is invalid";
    else if ($newEmail == $email)
      $error = "Same email entered.";
    else if ($result->num_rows)
      $error = "Email $newEmail is not available.";
    
    //if error show it, else query change
    if ($error != ''){
      echo "<script>".
            "var temp = confirm(\"$error\\nEmail not updated.\");" .
            "</script>";
    }
    else{
      $result = queryMysql("UPDATE users SET email = '$newEmail' WHERE id = $id");
      $_SESSION['email'] = $newEmail;
      $email = $newEmail;
      echo "<script>".
            "var temp = confirm(\"Email updated successfully!\");" .
            "</script>";
    }
  }
  
?>

<body>
  <div class="container">
  <?php echo "<h4 class=\"card-text\">$userstr</h4>"; ?>
  <button type="button" class="main_btn" onclick="location.href='main.php'">Home</button>
    <button type="button" class="main_btn logout_btn" onclick="location.href='index.php'">Log Out</button>
    <div class="card" >
      <img class="card-img-top" src="img/profile_maint.png" alt="Some Image">
      <div class="card-body">
        <h2 class="card-title" style="text-align:center;">Profile Maintenance</h5>
        <br>
        <div class="row">
          <div class="col-sm-6"><h5 class="card-text"><b>User Name</b></h5></div>
          <div class="col-sm-6"><h5 class="card-text"><b>Email</b></h5></div>
        </div>
        <div class="row">
<?php
  echo <<<_BODY1
        <div class="col-sm-6"><p>$user</p></div>
        <div class="col-sm-6"><p>$email</p></div>
_BODY1;
?>
        </div>
        <div class="row">
          <div class="col-sm-6">
            <div class="card" style="border:none;">
              <div class="card-body">
                <p><button class="btn btn-primary same-length" data-toggle="modal" data-target="#emailModalLabel">Edit Email</button></p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="card" style="border:none;">
              <div class="card-body">
                <p><button class="btn btn-primary same-length" data-toggle="modal" data-target="#passModalLabel">Edit Password</button></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <br>
  <!-- Email Edit Modal -->
  <div class="modal fade" id="emailModalLabel" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="border:none;">
          <h5 class="modal-title">Enter New Email</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="emailModalForm" method="post" action='profile_maint.php' role="form">
            <input type="text" placeholder="Enter new email" name="new_email" style="margin:auto;" maxlength="40" required><br>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" form="emailModalForm" class="btn btn-primary">Save changes</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Password Edit Modal -->
  <div class="modal fade" id="passModalLabel" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="border:none;">
          <h5 class="modal-title">Enter New Password</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="passModalForm" method="post" action='profile_maint.php' role="form">
            <input type="password" placeholder="Enter new password" name="new_pass" class="form-control" style="margin:auto;" maxlength="20" required>
            <br>
            <input type="password" placeholder="Re-enter new password" name="new_pass_re" class="form-control" style="margin:auto;" maxlength="20" required>
            <br>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" form="passModalForm" class="btn btn-primary">Save changes</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!-- upload modal -->
  <div class="modal fade" id="uploadModalLabel" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="border:none;">
          <h5 class="modal-title">Publication Upload</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <!-- Upload Modal BODY -->
        <div class="modal-body">
          <form id="uploadModalForm" action="profile_maint.php" method="post" enctype="multipart/form-data" style="margin:10px;">
            <div class="form-group row">
            `<input type="file" name="file" required>
            </div>
            <div class="form-group row">
              <input type="text" placeholder="Title" name="pub_title" style="margin:auto;" maxlength="100" required>
            </div>
            <div class="form-group row">
              <input type="text" placeholder="Author" name="pub_author" style="margin:auto;" maxlength="45" required>
            </div>
            <div class="form-group row">
              <label for="date-input" class="col-2 col-form-label">Published</label>
              <div class="col-10"><input id="date-input" class="form-control" type="date" name="pub_date"></div>
            </div>
            <div class="form-group row">
              <input type="text" placeholder="Category" name="pub_category" style="margin:auto;" maxlength="45" required>
            </div>
            <div class="form-group row">
              <textarea class="form-control" placeholder="Enter notes or a description of the publication." name="pub_notes" maxlength="65000" rows="5" required></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" form="uploadModalForm" class="btn btn-primary" name="upload" value="upload">Upload</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Publications -->
  <div class="container" >
    <div class="box-header clearfix">
      <div class="left-cell">
        <h2 class="card-title">Your Publications</h2>
      </div>
      <div class="right-cell">
        <button class="btn btn-primary" style="white-space: normal;
" data-toggle="modal" data-target="#uploadModalLabel"><i class="fa fa-plus"></i> Add New</button>
      </div>
    </div>
    <!-- User Publications -->
<?php
  $query = queryMysql("SELECT * FROM pubs WHERE user_id='$id' ORDER BY id DESC");
  if($query->num_rows > 0){
    while($row = $query->fetch_assoc()){
      $pubId    = $row['id'];
      $uploaded = $row['upload_date'];
      $title    = $row['title'];
      $notes    = $row['notes'];
      $author   = $row['author'];
      $pubDate  = $row['published'];
      $category = $row['category'];
      $filePath = $row['file_path'];
      $fileName = $row['file_name'];
      $fileType = strtoupper(pathinfo($filePath,PATHINFO_EXTENSION));
      
      //change date format
      $uploaded = date("F j, Y", strtotime($uploaded));
      if($pubDate = "0000-00-00") 
        $pubDate = "-";
      else
        $pubDate = date("F j, Y", strtotime($pubDate));
      
      //print out publication
      echo <<<_PUB
        <div class="card mb-3 card-color">
          <div class="card-body">
              <p>ID: $pubId</p>
              <p>Uploaded: $uploaded</p>
		    			<h3 class="card-title">$title</h3>
		    			<p class="card-text hide-chars">$notes</p>
              <p>Author: $author</p>
              <p>Published: $pubDate</p>
              <p>Category: $category</p>
              <p>File Type: $fileType</p>
              <form action="profile_maint.php" method="post">
                <a href="$filePath" target="_blank" class="card-link">View
                  <button type="submit" name="delete_pub" class="btn btn-primary" style="margin-left:10px;">Delete</button>
                </a>
                <input type="hidden" name="pub_id" value="$pubId">
                <input type="hidden" name="title" value="$title">
                <input type="hidden" name="file_name" value="$fileName">
                <input type="hidden" name="file_path" value="$filePath">
              </form>
          </div>
        </div>
_PUB;
    }
  }
?> 
	</div>
  <br>
</body>
</html>
