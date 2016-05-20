<?php session_start(); ?>

  <?php
include_once('PDO.class.php');
$db = New database();
$data = $db->query('SELECT * FROM galery WHERE login = \'' . $_SESSION['user_session'] . '\'');
  foreach ($data as $value) {
    ?>
    <div id="plus">
      <img id="img" value= <?php echo "\"" . $value['src'] . "\""; ?> onclick=<?php
 $source = $value['src'];
  print "\"deleteImg(" . "[" . $value['src'] . ", " . $value['login'] . "])\"";
  ?>
  <?php print "src=\"" . $value['src'] . '"';?> >
  <div id="comment" >
    <?php
    $source = $value['src'];
    $com = $db->query('SELECT login, com FROM com WHERE src = \'' . $source . '\'');
    foreach ($com as $value) {
      echo "<h3>" . "<i>" . $value['login'] . ":</i>\n" . $value['com'] . "</h3>";
    }
     ?>
  </div>
  <button class="heart" id="heart" <?php echo "name=\"" . $value['src'] . "cousin\" " . "onclick=\"recup('" . $source . "')\"" ?> >
  </button>
  <button class="comment" id="comment" value="comment?" onclick=<?php echo "\"comment(['" . $_SESSION['loggued_on_user'] . "', '" . $source . "'])\"" ?> >
  </button>
</div>
      <?php
  }
  ?>
