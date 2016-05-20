<?php
  include_once("PDO.class.php");
  $f = file_get_contents("php://input");
  $file = explode(",", $f);
  $data = new galery();
  $answers = $data->comment($file[1], $file[0], $file[2]);
  var_dump($answers);

 ?>
