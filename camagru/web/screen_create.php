<<?php
//header ("Content-type: image/jpeg");
$f = file_get_contents("php://input");
$file = explode(',', $f);

//die(var_dump($file));
define('UPLOAD_DIR', 'images/galerie/');
var_dump('voici l\'item');
var_dump($file[2]);
  $img = $file[1];
  $img = str_replace(' ', '+', $img);
  $files = UPLOAD_DIR . uniqid() . '.png';
if ($file[2])
{
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
    $item = imagecreatefrompng('images/style/tableau.png');
    $truc = getimagesize('images/style/tableau.png');
    $heigth = $truc[1];
    $width = $truc[0];
}
  else if ($file[2] == 'mickey')
  {
    $item = imagecreatefrompng('images/style/mickey.png');
    $truc = getimagesize('images/style/mickey.png');
    $heigth = $truc[1];
    $width = $truc[0];
  }
  if ($file[2] == 'mickey' || $file[2] == 'lapin')
    $item = imagecolortransparent($item, $white);
/*  if ($file[2] == 'cadre')
    $item = imagecolortransparent($item, $black);
  */
//die(var_dump($file[2]));
  $data = imagecreatefromstring(base64_decode($img));
    imagecopy($data, $item, 50, 30, 0, 0, $width, $heigth);
    imagepng($data, $files);
}
else {
  $success = base64_decode($img);
$successi = file_put_contents($files, $success);
print $successi ? $file : 'Unable to save the file.';
  //$name = "simualtion de nom en fonction heure date milisecondes";
}

 ?>
