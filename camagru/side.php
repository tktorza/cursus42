<side>
  <?php include_once('users/user_connect.php') ?>
<div class="side">
  <?php
  include_once('PDO.class.php');
  $db = New database();
  $data = $db->query('SELECT * FROM galery WHERE login = \'' . $_SESSION['user_session'] . '\'');
    foreach ($data as $value) {
      ?>
      <div id="galery">
        <img id="galery" <?php print "src=\"" . $value['src'] . '"';?> >
      </div>
        <?php
    }
   ?>
<script>
</script>
</side>
