<?php
session_start();
include_once("PDO.class.php");
$f = file_get_contents("php://input");
//faire la verif si le fichier est deja like par ce pseudo.
$galery = new galery();
$answers = $galery->liked($f, $_SESSION['loggued_on_user']);
var_dump($answers);
 ?>
