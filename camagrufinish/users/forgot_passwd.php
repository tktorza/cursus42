<<?php
  include_once('../PDO.class.php');
  $user = new User();
  if($user->forgot($_POST['login']))
    echo "ok!";
  else {
    echo "error!";
  }
 ?>
