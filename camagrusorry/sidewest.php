<?php
session_start();
?>

  <?php
include_once('PDO.class.php');
$db = New database();
$data = $db->query('SELECT * FROM galery WHERE login = \'' . $_SESSION['user_session'] . '\'');
  foreach ($data as $value) {
    ?>
    <div id="img">
      <img id="img" onclick="deleteImg(<?php echo $value; ?>)" <?php print "src=\"" . $value['src'] . '"';?> >
    </div>
      <?php
  }
  ?>
