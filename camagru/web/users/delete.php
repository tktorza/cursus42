<?php
  header('Location:../camagru.php');
  session_start();
  include_once('../PDO.class.php');
  $user = new users();
  if ($user->delete($_SESSION['loggued_on_user']))
  {
    echo "true";
    $user->logout();
}
  else
  echo "false";
 ?>
