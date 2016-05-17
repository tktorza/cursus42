<?php
header("Location:../camagru.php");

session_start();
include_once('../PDO.class.php');
	$user = new users();
	if ($_POST['submit'] == "forgot password?"){
		if($user->forgot($_POST['login']))
	    {
				$var = "passwd=true";
			echo "ok!";
	  }else {
			$var = "passwd=false";
			  echo "error!";
	}}
	else{

	if ($user->login($_POST["login"], $_POST["passwd"]) == true)
	{
		$var = "";
		$_SESSION["loggued_on_user"] = $_POST["login"];
	}
	else
	{
		$var = "";
		$_SESSION["loggued_on_user"] = "";
		echo "ERROR\n" . $user->login($_POST["login"], $_POST["passwd"]) . 'bad\n';
	}

}
?>
