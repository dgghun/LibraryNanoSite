<?php //checkemail.php
  require_once 'functions.php';

  if (isset($_POST['email']))
  {
    $email   = sanitizeString($_POST['email']);
    $result = queryMysql("SELECT * FROM users WHERE email='$email'");

    $trimmed = trim($email);
    if ($trimmed = ''){//do nothing
    }
    else if ($result->num_rows){
      echo  "<span class='taken'>&nbsp;&#x2718; " .
            "The email '$email' is used. Have an account already?</span>";
    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
      echo  "<span class='taken'>&nbsp;&#x2718; " .
            "The Email address is invalid</span>";
    }
    else
      echo "<span class='available'>&nbsp;&#x2714; " .
           "Your email '$email' is unique</span>";
  }
?>
