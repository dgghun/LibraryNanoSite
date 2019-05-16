<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Style Sheets -->
	<link rel="stylesheet" href="_bootstrap_4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/main.css">
  
	<!-- Scripts-->
	<script src="_popper_1.14.7/popper.min.js"></script>
	<script src="_jquery_3.3.1/jquery.min.js"></script>
	<script src="_bootstrap_4.3.1/js/bootstrap.min.js"></script>
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
    $_SESSION['timestamp'] = $time;
    echo "<script>".
                "var temp = confirm(\"Inactivity timeout.\\nPlease log in to use site.\");" .
                "window.location.assign('index.php');" .
         "</script>";
    
  }
  
  //Check if a session is set, if not exit
  $userstr = '';
  if (isset($_SESSION['user']))
  {
    $user     = $_SESSION['user'];
    $email    = $_SESSION['email'];
    $id    = $_SESSION['id'];
    $userstr  = "Hello $user!";
    $_SESSION['timestamp'] = $time;
  }
  else {
    echo "<script>".
                "var temp = confirm(\"Please log in to use site.\");" .
                "window.location.assign('index.php');" .
         "</script>";
  }
?>
  <body class="bkground">
    <div class="container">
      <?php echo "<h4 class=\"card-text\">$userstr</h4>"; ?>
      <button type="button" class="profile_btn" onclick="location.href='profile_maint.php'">Profile</button>
      <button type="button" class="profile_btn logout_btn" onclick="location.href='index.php'">Log Out</button>
      <div><img class="card-img-top" src="img/img_logo.png" alt="Some Image"></div>
      <h1 class="card-title" style="text-align:center;">Publications</h1>
      
<?php
  $query = queryMysql("SELECT * FROM pubs ORDER BY id DESC");
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
              <a href="$filePath" target="_blank" class="card-link">View</a>
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
