<?php include('users/user_connect.php'); $_POST['error'] = 0; $_GET['idmin'] = 0; $_GET['idmax'] = 10;?>
<HTML>
	<HEAD>
		<TITLE>Camagru</TITLE>
 <link rel="stylesheet" type="text/css" href="camagru.css" media="screen">
	</HEAD>
	<BODY class="fond">
	<?php include('header.php');
	include('refreachgalery.php');
	include('footer.php');?>

<script>



	function previouspage(tab){
		var idmin = "<?php echo $_GET['idmin']; ?>",
				idmax = "<?php echo $_GET['idmax']; ?>";

		var min = tab[0] - 10,
		 		max = tab[1] - 10;

		var str = "idmin=" + min + "&idmax=" + max;
		showImg(str);
	}

	function nextpage(tab){
	//	var idmin = document.querySelector('#idmin').value,
		//		idmax = document.querySelector('#idmax').value;
				var idmin = "<?php echo $_GET['idmin']; ?>",
						idmax = "<?php echo $_GET['idmax']; ?>";
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
	var idmin = "<?php echo $_GET['idmin']; ?>",
			idmax = "<?php echo $_GET['idmax']; ?>";

	var reponse = confirm("are your sur to want to delete this picture?");
	if (reponse){
			var ajax = new XMLHttpRequest();
			ajax.open("POST", "deleteimage.php", true);
			ajax.send(src);
	}
	else {
		console.log("action annulee");
	}
	showImg("idmin=" + idmin + "&idmax=" + idmax);

}


function comment(tab){
	var idmin = "<?php echo $_GET['idmin']; ?>",
			idmax = "<?php echo $_GET['idmax']; ?>";
	var login = tab[0],
			src		= tab[1];
	var com = prompt("Enter com");
	var all = [login, src, com];
	var ajax = new XMLHttpRequest();
	ajax.open("POST", "comment.php", true);
	ajax.send(all);
//	showImg("frf");------------------------------------------> to refreah when i do.
	showImg("idmin=" + idmin + "&idmax=" + idmax);
}



//var image = document.querySelector('#img');
//var heart = document.querySelector("#heart");

//image.addEventListener('click', function(){
//	alert(image.src);
//	showImg(str);
//}, false);

function liker(src){
	var idmin = "<?php echo $_GET['idmin']; ?>",
			idmax = "<?php echo $_GET['idmax']; ?>";
	var ajaxi = new XMLHttpRequest();
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
	var ret = liker(src);
	var name = (src.split('/')[2]).split('.')[0];
	//console.log("button[name=\""+name+"\"]");
	var image = document.querySelector("button[name=\""+name+"\"]");
	//console.log(image);

	/*if ("" + ret === "bool(true)\n") {
		image.style.backgroundColor = "red";
		image.style.color = "red";
		//colorier l'image en rouge;

	} else {
		image.style.backgroundColor = "grey";
		image.style.color = "grey";
		//colorier l'image en gris;
	}*/
	var idmin = "<?php echo $_GET['idmin']; ?>",
			idmax = "<?php echo $_GET['idmax']; ?>";
			showImg("idmin=" + idmin + "&idmax=" + idmax);
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
