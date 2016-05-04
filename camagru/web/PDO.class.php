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
      function start(){
        $this->_db->query('use sql5118032');
        $this->_db->query('CREATE TABLE users (
            id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
            login VARCHAR(255) NOT NULL,
            passwd VARCHAR(255),
            email VARCHAR(255),
            PRIMARY KEY (id)
          )');
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
      $control = $this->db->prepare('SELECT login FROM users WHERE login = :login');
      $control->execute(array(':login' => $login));
      $result = $control->fetchAll(PDO::FETCH_ASSOC);
      if (!$result)
        {
        $control = $this->db->prepare("INSERT INTO users VALUES (NULL, :login, :pass_hash, :mail)");
        $control->execute(array(':login' => $login, ':pass_hash' => $pass_hash, ':mail' => $mail));
        mail("$mail", "INSCRIPTION REUSSIE", "Bonjour,\nVotre inscription au site Camagru s'est parfaitement deroulee.\nVotre login:" . "$login" . PHP_EOL . "Votre mot de pass :" . "$passwd" . "\nBienvenue de la part de toute l'equipe Camagru!");
        }
      else
        return NULL;
     }

  function login($login, $passwd){
      $base = $this->db->query('SELECT * FROM users');
      $new = $base->fetchAll(PDO::FETCH_ASSOC);
    foreach ($new as $value) {
      if ($value['login'] === $login && hash("whirlpool", $passwd) === $value['passwd'])
      {
        $_SESSION['user_session'] = $login;
        return true;
      }
    }
    $_SESSION['user_session'] = "";
    return false;
  }

  function modif($login, $oldpw, $newpw){
    $base = $this->db->prepare('SELECT * FROM users WHERE login = :login');
    if ($base){
    $base->execute(array(':login' => $login));
    $result = $base->fetchAll(PDO::FETCH_ASSOC);
    $passwdagain = hash("whirlpool", $newpw);
    if ($result)
    {
      if (hash("whirlpool", $oldpw) == $result[0]['passwd'])
      {
         $base = $this->db->prepare('UPDATE users SET passwd = :passwdagain WHERE login = :login');
         $base->execute(array(':passwdagain' => $passwdagain, ':login' => $login));
         return true;
    }
  }}
      return false;
}

  function delete($login){
    $result = $this->db->prepare('DELETE FROM users WHERE login = :login');
    $result->execute(array(':login' => $login));
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
