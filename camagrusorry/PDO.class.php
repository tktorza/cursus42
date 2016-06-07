<?php

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
        $this->_db->query('use camagru');
        $this->_db->query('CREATE TABLE users (
            id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
            login VARCHAR(255) NOT NULL,
            passwd VARCHAR(255),
            email VARCHAR(255),
            PRIMARY KEY (id)
          )');
        $this->_db->query('CREATE TABLE galery (
          id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
          login VARCHAR(255) NOT NULL,
          src VARCHAR(255) NOT NULL,
          likes INT UNSIGNED,
          loginwholike MEDIUMTEXT,
          PRIMARY KEY (id)
        )');
        $this->_db->query('CREATE TABLE com (
          id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
          login VARCHAR(255) NOT NULL,
          src VARCHAR(255) NOT NULL,
          com MEDIUMTEXT,
          PRIMARY KEY (id)
        )');
        return true;
      }

      function query($sentence){
        $elem = $this->_db->query($sentence);
        return $elem;
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

     function forgot($login)
     {
       if ($login)
       {
         $passwd = uniqid();
           $pass_hash = hash("whirlpool", $passwd);
       $this->db->query('UPDATE users SET passwd =\'' . $pass_hash . '\' WHERE login =\'' . $login . '\'');
       $base = $this->db->query('SELECT email FROM users WHERE login =\'' . $login . '\'');
       $mail = $base->fetchAll(PDO::FETCH_ASSOC);
       mail($mail[0]['email'], "NEW PASSWORD", "Bonjour,\nVoici votre nouveau mot de passe:" . $passwd . ".\nNous vous invitons a le changer en vous connectant et choisissant le bouton modifier le mot de passe.\nA bientot, l'equipe Camagru.");
       return true;
     }
     else {
       return false;
     }
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

//si il y a une PDO ERREUR regarde nombre arg de galery

 /* class who done all pictures and items of site.
*/
class galery extends users
{
  private $db;

  function __construct()
  {
    global $dbase;
    $this->db = $dbase->_db;
  }

  function post($login, $src)
  {
    $control = $this->db->prepare('INSERT INTO galery VALUES (NULL, :login, :src, 0, NULL)');
    $control->execute(array(':login' => $login, ':src' => $src));
  }

  function deleting($login, $src)
  {
    $result = $this->db->prepare('DELETE FROM galery WHERE login = :login && src = :src');
    $result->execute(array(':login' => $login, ':src' => $src));
  }

  function like($login, $src){
    $base = $this->db->query('SELECT * FROM galery WHERE src = \'' . $src . '\'');
    $new = $base->fetchAll(PDO::FETCH_ASSOC);
    if ($new)
    {
      $tab = explode(' ', $new[0]['loginwholike']);
      foreach ($tab as $value) {
        if ($value === $login)
          {
            $result = str_replace($login, "", $tab);
            $result = implode(" ", $result);
            $control = $this->db->prepare('UPDATE galery
            SET loginwholike = :login, likes = likes - 1 WHERE src = :src');
            $value = $control->execute(array(':login' => $result, ':src' => $src));
            return false;
          }
      }
      $tmp = $new[0]['loginwholike'] . " " . $login;
      $control = $this->db->prepare('UPDATE galery
      SET loginwholike = :login, likes = likes + 1 WHERE src = :src');
      $value = $control->execute(array(':login' => $tmp, ':src' => $src));
      return $value;
    }
    else {
      return false;
    }

  }

  function liked($src, $login){
    $base = $this->db->query('SELECT loginwholike FROM galery WHERE src = \'$src\'');
    $string = $base->fetchAll(PDO::FETCH_ASSOC);
    $tab = explode(' ', $string[0]);
    foreach ($tab as $value) {
      if ($value == $login)
        return "true";
    }
    return "false";
  }

  function comment($src, $login, $com){
    $base = $this->db->prepare('INSERT INTO com VALUE (NULL, :login, :src, :com)');
    $result = $base->execute(array(':login' => $login, ':src' => $src, ':com' => $com));
    if ($result){
      $data = $this->db->query('SELECT email FROM users WHERE login = \'' . $login . "'");
      $mail = $data->fetchAll(PDO::FETCH_ASSOC);
      mail($mail[0]['email'], "NOUVEAU COM", "Camagru, \nNouveau com sur l'une de vos photo!");
    }
    return $result;
  }

function del($src, $login){
  $base = $this->db->prepare('SELECT login FROM galery WHERE src = :src');
  $same = $base->execute(array(':src' => $src));
  if ($same == $login){
    $base = $this->db->prepare('DELETE FROM galery WHERE src = :src');
    $same = $base->execute(array(':src' => $src));
    if ($same){
      $base = $this->db->prepare('DELETE FROM com WHERE src = :src');
      $same = $base->execute(array(':src' => $src));
      if ($same)
        return true;
    }
  }
  return false;
}

}
 ?>
