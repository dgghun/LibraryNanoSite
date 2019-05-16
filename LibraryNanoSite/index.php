<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- style sheets -->
  <link rel="stylesheet" href="_bootstrap_4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/index.css">
  
  <!-- scripts -->
  <script src="_jquery_3.3.1/jquery.min.js"></script>
  <script src="_bootstrap_4.3.1/js/bootstrap.min.js"></script>
  <script src="js/login.js"></script>
</head>

<?php
  session_start();
  require_once 'functions.php';
  
  //if session set, restart it.
  if (isset($_SESSION['user']))
  {
    session_unset();
    session_destroy();
    session_start();
    $loggedin = FALSE;
  }
  
  $error = $user = $pass = "";
  
  //Check login credentials and log user in
  if (isset($_POST['user']))
  {
    $user = sanitizeString($_POST['user']);
    $pass = sanitizeString($_POST['pass']);
    
    if ($user == "" || $pass == "")
      $error = 'Not all fields were entered';
    else
    {
      $pass = hashPass($pass); // hash the password
      $result = queryMySQL("SELECT username,id,email FROM users
        WHERE username='$user' AND password='$pass'");
  
      if ($result->num_rows == 0)
      {
        $error = "Invalid login attempt";
      }
      else
      {
            $row = mysqli_fetch_object($result);
            $id = $row->id;
            $email = $row->email;
        
        $_SESSION['user'] = $user;
        $_SESSION['email'] = $email;
        $_SESSION['id'] = $id;
        $_SESSION['timestamp'] = $_SERVER['REQUEST_TIME'];
        echo "<script>".
                  "window.location.assign('main.php');" .
                "</script>";;
      }
    }
  }
  
  echo <<<_BODY
  <body>
    <div class="jumbotron">
    <div class="grandParentContaniner">
      <div class="parentContainer">
        <h1 align="center">Library Pub</h1>
        <form method='post' action='index.php'>
        <div class="imgcontainer">
          <img src="img/img_logo.png" alt="logo" class="logo">
        </div>
        <div class="container">
          <div style="text-align:center;"><span class='error'>$error</span></div>
          <label for="uname"><b>Username</b></label>
          <input type="text" placeholder="Enter Username" name="user" maxlength="16" required>
          <label for="psw"><b>Password</b></label>
          <input type="password" placeholder="Enter Password" name="pass" maxlength="20" required>
          <button type="submit">Login</button>
        </div>
        <div class="container" >
          <button type="button" class="signupbtn" id="signup" onclick="location.href='signup.php'">Sign Up!</button>
          <!-- <span class="psw"><a href="#">Forgot password?</a></span> -->
        </div>
        </form>
      </div>
    </div>
    </div>
  </body>
_BODY;
?>
</html>
