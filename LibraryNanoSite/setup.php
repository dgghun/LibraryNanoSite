<!DOCTYPE html>
<html>
  <head>
    <title>Setting up database</title>
  </head>
  <body>

    <h3>Setting up...</h3>

<?php // setup.php
  require_once 'functions.php';

  // use this to drop tables and start over.
  //echo "Dropping `pubs` table...<br>";
  //queryMysql('DROP TABLE `pubs`;');
  //echo "Dropping `users` table...<br>";
  //queryMysql('DROP TABLE `users`;');

  //Table structure for table pubs
  createTable('pubs',
    'id int(10) UNSIGNED NOT NULL,
    user_id int(10) UNSIGNED NOT NULL,
    upload_date date NOT NULL,
    title varchar(100) NOT NULL,
    notes longtext,
    author varchar(45) DEFAULT NULL,
    published date DEFAULT NULL,
    category varchar(45) DEFAULT \'General\',
    file mediumblob NOT NULL,
    file_name varchar(45) NOT NULL',
   'ENGINE=InnoDB DEFAULT CHARSET=latin1');
  
  //Table structure for table users
  createTable ('users',
    'id int(10) UNSIGNED NOT NULL,
    username varchar(45) NOT NULL,
    email varchar(45) NOT NULL,
    password varchar(45) NOT NULL',
    'ENGINE=InnoDB DEFAULT CHARSET=latin1');
  
  //Indexes for table pubs
  echo "Indexes for table pubs...<br>";
  queryMysql('ALTER TABLE `pubs`
              ADD PRIMARY KEY (`id`),
              ADD KEY `user_id` (`user_id`);'
              );
              
  //Indexes for table users
  echo "Indexes for table users...<br>";
  queryMysql('ALTER TABLE `users`
              ADD PRIMARY KEY (`id`),
              ADD KEY `id` (`id`);'
              );
  
  //auto_increment for table pubs
  echo "Auto_increment for table pubs ...<br>";
  queryMysql('ALTER TABLE `pubs`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;'
              );
  
  //auto_increment for table users
  echo "Auto_increment for table users ...<br>";
  queryMysql('ALTER TABLE `users`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;'
              );

  //Constraints for table pubs
  echo "Constraints for table pubs ...<br>";
  queryMysql('ALTER TABLE `pubs`
              ADD CONSTRAINT `pubs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);'
              );
              
?>

    <br>...done.
  </body>
</html>
