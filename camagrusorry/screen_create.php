<<?php
include('users/user_connect.php');
$f = file_get_contents("php://input");
include_once('PDO.class.php');
$file = explode(',', $f);
define('UPLOAD_DIR', 'images/galerie/');
var_dump('voici l\'item');
var_dump($file);
  $img = $file[1];
  if ($file){
  if (file_get_contents($file[0]))
{
  if ($file[0][strlen($file[0]) - 2] == 'n')
    $data = imagecreatefrompng($file[0]);
  else if ($file[0][strlen($file[0]) - 2] == 'e')
    $data = imagecreatefromjpeg($file[0]);
  else {
      throw new Exception("Mauvais fichier.", 1);
      exit();

  }
    $screen_width = $file[2];
    $screen_height = $file[3];
    $im = imagecreatetruecolor(55, 30);
    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);
    if ($file[1] == 'lapin')
    {
      $item = imagecreatefrompng('images/style/oreille-lapin.png');
      $truc = getimagesize('images/style/oreille-lapin.png');
      $heigth = $truc[1];
      $width = $truc[0];


    }
    else if ($file[1] == 'cadre')
    {
    //  $pattern = imagecreatetruecolor($screen_width, $screen_height);
      //$type = mime_content_type($img);
      $item = imagecreatefrompng('images/style/tableau.png');
      $truc = getimagesize('images/style/tableau.png');
      $heigth = $truc[1];
      $width = $truc[0];
      //imagecopyresampled($pattern, $item, 0, 0, 0, 0, $screen_width, $screen_heigth, $heigth, $width);
      //$item = imagepng($pattern);
  }
    else if ($file[1] == 'mickey')
    {
      $item = imagecreatefrompng('images/style/mickey.png');
      $truc = getimagesize('images/style/mickey.png');
      $heigth = $truc[1];
      $width = $truc[0];
    }
  }
  else {
  $img = str_replace(' ', '+', $img);
  $data = imagecreatefromstring(base64_decode($img));
  $screen_width = $file[3];
  $screen_height = $file[4];

  $im = imagecreatetruecolor(55, 30);
  $white = imagecolorallocate($im, 255, 255, 255);
  $black = imagecolorallocate($im, 0, 0, 0);
  if ($file[2] == 'lapin')
  {
    $item = imagecreatefrompng('images/style/oreille-lapin.png');
    $truc = getimagesize('images/style/oreille-lapin.png');
    $heigth = $truc[1];
    $width = $truc[0];


  }
  else if ($file[2] == 'cadre')
  {
  //  $pattern = imagecreatetruecolor($screen_width, $screen_height);
    //$type = mime_content_type($img);
    $item = imagecreatefrompng('images/style/tableau.png');
    $truc = getimagesize('images/style/tableau.png');
    $heigth = $truc[1];
    $width = $truc[0];
    //imagecopyresampled($pattern, $item, 0, 0, 0, 0, $screen_width, $screen_heigth, $heigth, $width);
    //$item = imagepng($pattern);
}
  else if ($file[2] == 'mickey')
  {
    $item = imagecreatefrompng('images/style/mickey.png');
    $truc = getimagesize('images/style/mickey.png');
    $heigth = $truc[1];
    $width = $truc[0];
  }
  //if ($file[2] == 'mickey' || $file[2] == 'lapin')
    //$item = imagecolortransparent($item, $white);
/*  if ($file[2] == 'cadre')
    $item = imagecolortransparent($item, $black);
  */
//die(var_dump($file[2]));
}

$files = UPLOAD_DIR . uniqid() . '.png';

    imagecopy($data, $item, 50, 30, 0, 0, $width, $heigth);
    imagepng($data, $files);
    $galery = new galery();
    var_dump("slt");
    $galery->post($_SESSION['user_session'], $files);
    $successi = "SUCCESS\n";
}
else{
  throw new Exception('Image non enregistree.');
$successi = 0;
  //$name = "simualtion de nom en fonction heure date milisecondes";
}
print $successi ? $file : 'Unable to save the file.';

 ?>
