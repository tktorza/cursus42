<<?php
session_start();
include_once("PDO.class.php");
$data = file_get_contents("php://input");
$db = new galery();
$answers = $db->del($data, $_SESSION['loggued_on_user']);
var_dump($answers);
 ?>
