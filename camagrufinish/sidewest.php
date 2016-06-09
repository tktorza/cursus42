<?php
session_start();
?>

<?php
include_once('PDO.class.php');
$dbas = New database();
$maxi = ($dbas->query('SELECT MAX(id) FROM galery'))->fetchAll(PDO::FETCH_ASSOC)[0]['MAX(id)'];
include_once('PDO.class.php');
$db = New database();
$maximum = $maxi - 5;
$data = $db->query('SELECT * FROM galery WHERE login = \'' . $_SESSION['user_session'] . '\' && id > ' . $maximum);
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
