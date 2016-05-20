<?php
include('users/user_connect.php');
$f = file_get_contents("php://input");
if (file_get_contents($f))
  echo "true";
else {
  echo "false";
}
?>
