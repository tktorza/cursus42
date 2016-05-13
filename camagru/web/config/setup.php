<?php
  include_once('../PDO.class.php');
  $db = new database();
  if ($db->start())
    echo "everything's ok!";
  else {
    echo "KO :(";
  }
 ?>
