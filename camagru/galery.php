<?php include('users/user_connect.php'); $_POST['error'] == 0;?>
<HTML>
	<HEAD>
		<TITLE>Camagru</TITLE>
 <link rel="stylesheet" type="text/css" href="camagru.css" media="screen">
	</HEAD>
	<BODY >
	<?php include('header.php');?>
	<div  id="galery">
	  <?php
	include_once('PDO.class.php');
	$db = New database();
	$data = $db->query('SELECT * FROM galery');
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
		<button id="heart" <?php
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
	</div>

		<?php	include('footer.html');?>

<script>

function deletepic(src){
	alert(src);

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
}


function comment(tab){
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
	//refreach js webpage
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
                document.getElementById("galery").innerHTML = xmlhttp.innerHTML;
            }
        };
        xmlhttp.open("GET","refreachgalery.php?pseudo="+str,true);
        xmlhttp.send();
    }
}

//var image = document.querySelector('#img');
//var heart = document.querySelector("#heart");

//image.addEventListener('click', function(){
//	alert(image.src);
//	showImg(str);
//}, false);

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
				xmlhttp.open("GET","sidewest.php?pseudo="+str,true);
				xmlhttp.send();
		}

}

function liker(src){
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
