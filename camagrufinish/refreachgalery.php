<?php session_start(); ?>
<div class="blue" id="galery">
  <?php
include_once('PDO.class.php');
$db = New database();
$data = $db->query('SELECT * FROM galery WHERE id < ' . $_GET['idmax'] . ' && id >= ' . $_GET['idmin']);
  foreach ($data as $value) {
    ?>
    <div id="plus">
    <div align="left">  <img id="imge" value= <?php echo "\"" . $value['src'] . "\""; ?> onclick=<?php
 $source = $value['src'];
  print "\"deleteImg(" . "[" . $value['src'] . ", " . $value['login'] . "])\"";
  ?>
  <?php print "src=\"" . $value['src'] . '"';?> ></div>
  <div id="com" align="right">
    <?php
    $source = $value['src'];
    $com = $db->query('SELECT login, com FROM com WHERE src = \'' . $source . '\'');
    foreach ($com as $val) {
      echo "<i>" . $val['login'] . ":</i>\n" . $val['com'] . "</br>";
    }
     ?>
  </div>
  <button id="heart" <?php
                            if ($value['loginwholike']){
                            $tableau = explode(" ", $value['loginwholike']);
                            foreach ($tableau as $valeur) {
                              if ($valeur == $_SESSION['loggued_on_user'])
                              echo "style=\"background-color: red;\" color=\"red\" ";
                            }
                          }
                          $toub = explode("/", $source);
                          $var = explode('.', $toub[2])[0];
                          echo "class=\"heart\" name=\"" . $var . "\" " . "onclick=\"recup('" . $source . "')\"";
                          ?> >
                          <?php echo "Likes : " . $value['likes'] . "aminches"; ?>
  </button>
  <button class="comment" id="comment" value="comment?" onclick=<?php echo "\"comment(['" . $_SESSION['loggued_on_user'] . "', '" . $source . "'])\"" ?> >
  </button>
  <?php
    $login = $value['login'];
   if ($_SESSION['loggued_on_user'] == $login)
      {
        ?><button class="delete" id="delete" onclick=<?php echo "\"deletepic('" . $source .  "')\"";?> > </button><?php
      }
      ?>
</div>
      <?php
  }
  ?>
  <?php if ($_GET['idmin'] > 0){ ?>
<button class="previous" id="previous" <?php echo "onclick=\"previouspage([" . $_GET['idmin'] . ", " . $_GET['idmax'] . "])\"";  ?> >Previous page</button>
<?php }
$max = ($db->query('SELECT MAX(id) FROM galery'))->fetchAll(PDO::FETCH_ASSOC)[0]['MAX(id)'];
if ($_GET['idmax'] <= $max){

 ?>
 <button class="next" id="next" <?php echo "onclick=\"nextpage([" . $_GET['idmin'] . ", " . $_GET['idmax'] . "])\"";  ?> >Next page</button>
<?php } ?>
</div>
