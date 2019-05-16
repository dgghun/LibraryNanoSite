<?php // functions.php
  $dbhost  = 'localhost';    
  $dbname  = 'library_db';   
  $dbuser  = 'userName';       
  $dbpass  = 'SomePassword123';    

  $connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
  if ($connection->connect_error) die("Fatal Error");

  if(isset($_POST['functionname'])){
    if($_POST['functionname'] =  'deletePubMysql') {
      $query = $_POST['arguments'][0];
      queryMysql($query);
      $filePath = $_POST['arguments'][1];
      if(file_exists($filePath))
        unlink($filePath);
    }
  }
  
  function createTable($name, $query, $options)
  {
    queryMysql("CREATE TABLE IF NOT EXISTS $name($query)$options");
    echo "Table '$name' created or already exists.<br>";
  }

  function queryMysql($query)
  {
    global $connection;
    $result = $connection->query($query);
    if (!$result) {
      echo ("Error: " . mysqli_error($connection) . "<br>");
      die("Fatal Error");
    }
    return $result;
  }

  function destroySession()
  {
    $_SESSION=array();

    if (session_id() != "" || isset($_COOKIE[session_name()]))
      setcookie(session_name(), '', time()-2592000, '/');

    session_destroy();
  }

  function sanitizeString($var)
  {
    global $connection;
    $var = strip_tags($var);
    $var = htmlentities($var);
    if (get_magic_quotes_gpc())
      $var = stripslashes($var);
    return $connection->real_escape_string($var);
  }
  
	function insertPubMysql($userId, $title, $notes, $author, $pubDate, $category, $filePath, $fileName){
    $queryStr = "INSERT into library_db.pubs ".
												 "(user_id,upload_date,title,notes,author,published,category,file_path,file_name)".
                         " VALUES ".
												 "('$userId',CURDATE(),'$title','$notes','$author','$pubDate','$category','$filePath','$fileName')"; 
		$result = queryMysql($queryStr);  
		return $result;
  }

  function hashPass($password){
    $salt1 = "1$5*H"; // front salt
    $salt2 = "dG@!";  // back salt
    $password = hash('ripemd128', '$salt1$password$salt2'); //hash password
    return $password;
  }

?>
