<?php include('users/user_connect.php'); $_POST['error'] = 0; $_GET['idmin'] = 0; $_GET['idmax'] = 10;?>
<HTML>
	<HEAD>
		<TITLE>Camagru</TITLE>
 <link rel="stylesheet" type="text/css" href="camagru.css" media="screen">
	</HEAD>
	<BODY >
	<?php include('header.php');?>

	<div  id="galery">
		<input id="idmin" value=<?php echo "\"" . $_GET['idmin'] . "\""; ?> />
		<input id="idmax" value=<?php echo "\"" . $_GET['idmax'] . "\""; ?> />
	  <?php
	include_once('PDO.class.php');
	$db = New database();
	$data = $db->query('SELECT * FROM galery WHERE id < ' . $_GET['idmax'] . ' && id >= ' . $_GET['idmin']);
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
		<h1>L'id est <?php echo $value['id']; ?></h1>
		<button id="heart" <?php
//ce qui fait beuguer
		$table = explode(" ", $value['loginwholike']);
		foreach ($table as $value) {
			if ($value == $_SESSION['loggued_on_user'])
				echo "background-color=\"red\" ";
		}
		//jusquici

														$toub = explode("/", $source);
														$var = explode('.', $toub[2])[0];
														echo "class=\"heart\" name=\"" . $var . "\" " . "onclick=\"recup('" . $source . "')\"";
														?> >
														<?php echo "Likes : " . $value['like']; ?>
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
	</div>


		<?php	}include('footer.html');?>

<script>



	function previouspage(tab){
		var idmin = document.querySelector('#idmin').value,
				idmax = document.querySelector('#idmax').value;

		var min = tab[0] - 10,
		 		max = tab[1] - 10;

		var str = "idmin=" + min + "&idmax=" + max;
		showImg(str);
	}

	function nextpage(tab){
		var idmin = document.querySelector('#idmin').value,
				idmax = document.querySelector('#idmax').value;
	var min = tab[0] + 10,
	 		max = tab[1] + 10;

	var str = "idmin=" + min + "&idmax=" + max;

	showImg(str);
	}

	function showImg(str) {
	    if (str == "") {
	        document.getElementById("galery").innerHTML = "";
	        return;
	    } else {
	        if (window.XMLHttpRequest) {
	            // code for IE7+, Firefox, Chrome, Opera, Safari
	            xmlhttp = new XMLHttpRequest();
	        } else {
	            // code for IE6, IE5
	            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	        }
	        xmlhttp.onreadystatechange = function() {
	            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
	                document.getElementById("galery").innerHTML = xmlhttp.responseText;
	            }
	        };
	        xmlhttp.open("GET","refreachgalery.php?" + str ,true);
	        xmlhttp.send();
	    }
	}


function deletepic(src){
	alert(src);
	var idmin = document.querySelector('#idmin').value,
			idmax = document.querySelector('#idmax').value;

	var reponse = confirm("are your sur to want to delete this picture?");
	if (reponse){
			var ajax = new XMLHttpRequest();
			ajax.open("POST", "deleteimage.php", true);
			ajax.send(src);
			ajax.onreadystatechange = function() {
				if (ajax.readyState == 4 && ajax.status == 200){
					alert(ajax.responseText);
				}
			}
	}
	else {
		console.log("action annulee");
	}
	showImg("idmin=" + idmin + "&idmax=" + idmax);

}


function comment(tab){
	var idmin = document.querySelector('#idmin').value,
			idmax = document.querySelector('#idmax').value;
	var login = tab[0],
			src		= tab[1];
	var com = prompt("Enter com");
	var all = [login, src, com];
	console.log(all);
	var ajax = new XMLHttpRequest();
	ajax.open("POST", "comment.php", true);
	ajax.send(all);
//	showImg("frf");------------------------------------------> to refreah when i do.
	ajax.onreadystatechange = function() {
			if (ajax.readyState == 4 && ajax.status == 200) {
					console.log(ajax.responseText);
			}
	};
	console.log("idmin = " + idmin + "idmax = " + idmax);
	showImg("idmin=" + idmin + "&idmax=" + idmax);
}



//var image = document.querySelector('#img');
//var heart = document.querySelector("#heart");

//image.addEventListener('click', function(){
//	alert(image.src);
//	showImg(str);
//}, false);

function liker(src){
	var idmin = document.querySelector('#idmin').value,
			idmax = document.querySelector('#idmax').value;
	var ajaxi = new XMLHttpRequest();
	console.log("slt les aminches!");
	console.log(src);
	ajaxi.open("POST", "like.php", false)
	ajaxi.send(src);

	if (ajaxi.readyState == 4 && ajaxi.status == 200) {
			var like = ajaxi.responseText;
			return like;
}
else {
	return "false";
}
}
// # d _
function recup(src) {
	console.log(src);
	var ret = liker(src);
	var name = (src.split('/')[2]).split('.')[0];
	//console.log("button[name=\""+name+"\"]");
	var image = document.querySelector("button[name=\""+name+"\"]");
	//console.log(image);

	if ("" + ret === "bool(true)\n") {
		image.style.backgroundColor = "red";
		image.style.color = "red";
		//colorier l'image en rouge;

	} else {
		image.style.backgroundColor = "grey";
		image.style.color = "grey";
		//colorier l'image en gris;
	}
};



/*heart.addEventListener('click', function(src){
		if (heart.style.backgroundColor == "red")
			heart.style.backgroundColor = "grey";
		else {
			heart.style.backgroundColor = "red";
		}

}, false);
*/

</script>
	</BODY>
</html>
