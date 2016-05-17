<side>
<div class="side" id="galery">
  <?php
include_once('PDO.class.php');
$db = New database();
$data = $db->query('SELECT * FROM galery WHERE login = \'' . $_SESSION['user_session'] . '\'');
  foreach ($data as $value) {
    ?>
    <div id="img">
      <img id="img" onclick=<?php
 $source = $value['src'];
  print "\"deleteImg(" . "[" . $value['src'] . ", " . $value['login'] . "])\"";
  ?>
  <?php print "src=\"" . $value['src'] . '"';?> >
    </div>
      <?php
  }
  ?>
</div>
<script>

</script>
</side>
