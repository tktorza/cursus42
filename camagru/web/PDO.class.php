<?php
  include('users/user_connect.php');
  include('config/database.php');

  class database{

    public $_db;

    function __construct() {
  try {
    global $DB_DSN, $DB_USER, $DB_PASSWORD;
        $this->_db = new PDO($DB_DSN, $DB_USER, $DB_PASSWORD);
        $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
     print "Erreur !: " . $e->getMessage() . "<br/>";
     die();
          }
        }
  }
  $dbase = new database();

  class users extends database{
    private $db;

    public function __construct(){
      global $dbase;
      $this->db = $dbase->_db;
    }

    public function register($login, $passwd, $mail)
    {
      $pass_hash = hash("whirlpool", $passwd);
      $control = $this->db->query('SELECT login FROM users WHERE login = \'' . $login . '\'');
      $result = $control->fetchAll(PDO::FETCH_ASSOC);
      if (!$result)
        {
        $this->db->query("INSERT INTO users VALUES (NULL, '$login', '$pass_hash', '$mail')");
        mail("$mail", "INSCRIPTION REUSSIE", "Bonjour,\nVotre inscription au site Camagru s'est parfaitement deroulee.\nVotre login:" . "$login" . PHP_EOL . "Votre mot de pass :" . "$passwd" . "\nBienvenue de la part de toute l'equipe Camagru!");
        }
      else
        return NULL;
     }

  function login($login, $passwd){
      $base = $this->db->query('SELECT * FROM users');
      $new = $base->fetchAll(PDO::FETCH_ASSOC);
    foreach ($new as $value) {
      if ($value['login'] == $login && hash("whirlpool", $passwd) == $value['passwd'])
      {
        $_SESSION['user_session'] = $login;
        return true;
      }
    }
    $_SESSION['user_session'] = "";
    return false;
  }

  function modif($login, $oldpw, $newpw){
    $base = $this->db->query('SELECT * FROM users WHERE login = "' . $login . '"');
    if ($base){
    $result = $base->fetchAll(PDO::FETCH_ASSOC);
    $passwdagain = hash("whirlpool", $newpw);
    if ($result)
    {
      if (hash("whirlpool", $oldpw) == $result[0]['passwd'])
      {
         $this->db->query('UPDATE users SET passwd = \'' . $passwdagain . '\' WHERE login = \'' . $login . '\'');
         return true;
    }
  }}
      return false;
}

  function delete($login){
    $result = $this->db->query('DELETE FROM users WHERE login = \'' . $login . '\'');
    return ($result);
  }

function is_loggedin()
  {
     if(isset($_SESSION['user_session']))
     {
        return true;
     }
  }

  function redirect($url)
   {
       header("Location: $url");
   }

   function logout()
  {
       session_destroy();
       unset($_SESSION['user_session']);
       return true;
  }
}

 ?>
