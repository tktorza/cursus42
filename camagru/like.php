<?php
session_start();
include('PDO.class.php');
$f = file_get_contents("php://input");
$galery = new galery();
$answers = $galery->like($_SESSION['loggued_on_user'], $f);
var_dump($answers);
 ?>
