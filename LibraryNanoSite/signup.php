<!DOCTYPE html>
<html>
<head>
  <?php
    require_once 'functions.php';
  ?>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Style Sheets -->
	<link rel="stylesheet" href="_font-awesome_4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/signup.css">
    
  <!-- Scripts -->
  <script src="_jquery_3.3.1/jquery.min.js"></script>
  <script src="_bootstrap_4.3.1/js/bootstrap.min.js"></script>
	<script src="js/signup.js"></script>
  
  <?php
  echo <<<_END
    <script>
      function checkUser(user)
      {
        if (user.value == '')
        {
          $('#user_used').html('&nbsp;')
          return
        }
  
        $.post
        (
          'checkuser.php',
          { user : user.value },
          function(data)
          {
            $('#user_used').html(data)
          }
        )
      }
      
      function checkEmail(email)
      {
        if (email.value == '')
        {
          $('#email_used').html('&nbsp;')
          return
        }
  
        $.post
        (
          'checkEmail.php',
          { email : email.value },
          function(data)
          {
            $('#email_used').html(data)
          }
        )
      }
    </script>  
_END;

  $error = $user = $email = $pass = "";
  if (isset($_SESSION['user'])) destroySession();

  if (isset($_POST['user']))
  {
    $user = sanitizeString($_POST['user']);
    $pass = sanitizeString($_POST['pass']);
    $email = sanitizeString($_POST['email']);

    if ($user == "" || $pass == "")
      $error = 'Not all fields were entered<br><br>';
    else if(strlen($user) < 5)
      $error = 'Usernames must be at least 5 characters<br><br>';
    else if (!preg_match("/^[a-zA-Z0-9_-]*$/",$user))
      $error = 'Only a-z, A-Z, 0-9, - and _ allowed in Usernames';
    else if(strlen($pass) < 6)
      $error = 'Passwords must be at least 6 characters<br><br>';
    else if (!preg_match("/[a-z]/",$pass) ||
             !preg_match("/[A-Z]/",$pass) ||
             !preg_match("/[0-9]/",$pass)
            )
      $error = 'Passwords require one each of a-z, A-Z and 0-9';
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL))
      $error = 'The Email address is invalid';
    else
    {
      $result = queryMysql("SELECT * FROM users WHERE username='$user'");

      if ($result->num_rows)
        $error = 'That username already exists<br><br>';
      else
      {
        $pass = hashPass($pass); // hash the password
        queryMysql("INSERT INTO users (username,email,password) VALUES('$user','$email','$pass')");
        echo "<script>".
                "var temp = confirm(\"User Added Successfully!\\nNow please log in to use site.\");" .
                "window.location.assign('index.php');" .
              "</script>";
      }
    }
  }
  ?>
</head>

<?php
echo <<<_BODY
<body>
	<form method='post' action='signup.php' autocomplete='off' style='max-width:500px;margin:auto'>
      <h1 align="center">Create An Account</h1>    
      <div class="imgcontainer">
        <img src="img/registration.png" alt="Image" class="logo">
      </div>
      <div style="text-align:center; color:red;">$error</div>
      <div id='user_used' style="text-align:center;">&nbsp;</div>
      <div id='email_used' style="text-align:center;">&nbsp;</div>

      <div class="input-container">
        <i class="fa fa-user-circle icon"></i>
        <input class="input-field" type="text" placeholder="Username" name="user" value='$user' onBlur='checkUser(this)' maxlength='16' required>
      </div>
      
      <div class="input-container">
        <i class="fa fa-envelope icon"></i>
        <input class="input-field" type="text" placeholder="Email" name="email" value='$email' onBlur='checkEmail(this)' maxlength='40' required>
      </div>
      
      <div class="input-container">
        <i class="fa fa-key icon"></i>
        <input class="input-field" type="password" placeholder="Password" name="pass" value='$pass' maxlength='20' required>
      </div>
      
      <button type="submit" class="btn">Register</button>
  </form>
</body>
_BODY;
?>
</html>
