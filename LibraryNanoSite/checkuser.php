<?php //checkuser.php
  require_once 'functions.php';

  if (isset($_POST['user']))
  {
    $user   = sanitizeString($_POST['user']);
    $result = queryMysql("SELECT * FROM users WHERE username='$user'");

    $trimmed = trim($user);
    if ($trimmed = ''){//do nothing
    }
    else if ($result->num_rows){
      echo  "<span class='taken'>&nbsp;&#x2718; " .
            "The username '$user' is taken</span>";
    } 
    else if(strlen($user) < 5){
      echo  "<span class='taken'>&nbsp;&#x2718; " .
            "Usernames must be at least 5 characters</span>";
    }
    else if (!preg_match("/^[a-zA-Z0-9_-]*$/",$user)){
      echo  "<span class='taken'>&nbsp;&#x2718; " .
            "Only a-z, A-Z, 0-9, - and _ allowed in Usernames</span>";
    }
    else
      echo "<span class='available'>&nbsp;&#x2714; " .
           "The username '$user' is available</span>";
  }
?>
