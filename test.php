<?php 
  include 'db.php';
  $Update = "UPDATE `member_data` SET `Role` = 'Member'";
  mysql_query($Update) or die(mysql_error());
?> 