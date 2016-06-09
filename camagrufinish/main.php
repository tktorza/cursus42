<main class="item bloc">
	<table class="item responsive">
		<tr>
			<td colspan="3">
				<i class="title">Please choose your item:</i>
			</td>
		</tr>
		<tr>
			<td class="tall responsive">
				<div onclick="Item('cadre')">
		<div align="center">
	<img src="images/style/tableau.png" class="tall responsive">
</div>
<div align="right">
		<input type="radio" name="option" id="cadre" value="cadre" checked>
</div>
	</div>
	</td>
		<td class="tall responsive">
			<div onclick="Item('lapin')">
			<img src="images/style/oreille-lapin.png" class="tall responsive">
			<input type="radio" name="option" value="lapin" id="lapin">
		</div>
		</td>
		<td class="tall responsive">
			<div onclick="Item('mickey')">
			<img src="images/style/mickey.png" class="tall responsive">
			<input type="radio" name="option" id="mickey" value="mickey">
		</div>
		</td>
	</tr>
</table>
		<SPAN id="cover" class="responsive">
			<video id="video" class="responsive"></video>
			<button id="startbutton" class="responsive">Prendre une photo</button>
			<input type="text" id="upload" class="responsive" placeholder="if u want to upload ur image" name="upload"/>
			<input type="submit" id="verify" class="responsive" value="exist?" name="verify" onclick="checking()"/>
			<canvas id="canvas" class="responsive"></canvas>
		</span>

		<img src="http://img0.mxstatic.com/wallpapers/b95b28b4e31057e253aac3472c0aed41_large.jpeg" class="other responsive" id="photo" alt="photo">
		<script type="text/javascript">

		var itm = "cadre";
		var upload = document.querySelector('#uploadfile');

		function Item(word){
			itm = word;
			if (word == "lapin")
				alert("Positionnez vous au centre le l'ecran svp")
		}

var truc = "";
		function checking(){
			var upload			 = document.querySelector('#upload'),
					verify			 = document.querySelector('#verify');

			if (upload.value != "")
			{
				console.log(upload.value);
				var ajax = new XMLHttpRequest();
				var link = "verify.php";
				ajax.open("POST", link, true);
				ajax.send(upload.value);
				ajax.onreadystatechange = function() {
				if (ajax.readyState == 4 && ajax.status == 200) {
//					console.log(ajax.responseText);
					if (ajax.responseText == "true")
					{
							verify.style.backgroundColor = "green";
							verify.value = "Exists";
							truc = upload.value;
					}
				else {
					verify.style.backgroundColor = "red";
					verify.value = "No exists";
					upload.placeholder = "Try again";
					upload.value = "";
					return;
				}	}
			};
		}

		}

		(function(){



			var streaming = false,
		      video        = document.querySelector('#video'),
		      cover        = document.querySelector('#cover'),
		      canvas       = document.querySelector('#canvas'),
		      photo        = document.querySelector('#photo'),
		      startbutton  = document.querySelector('#startbutton'),
		      width = screen.width / 4,
		      height = screen.height / 4;

			navigator.getMedia = ( navigator.getUserMedia ||
				                       navigator.webkitGetUserMedia ||
				                       navigator.mozGetUserMedia ||
				                       navigator.msGetUserMedia);

		navigator.getMedia(
											     {
											       video: true,
											       audio: false
											     },
											     function(stream) {
											       if (navigator.mozGetUserMedia) {
											         video.mozSrcObject = stream;
											       } else {
											         var vendorURL = window.URL || window.webkitURL;
											         video.src = vendorURL.createObjectURL(stream);
											       }
											       video.play();
											     },
											     function(err) {
											       console.log("An error occured! " + err);
											     }
		);

		video.addEventListener('canplay', function(ev){
    if (!streaming) {
      height = video.videoHeight / (video.videoWidth/width);
      video.setAttribute('width', width);
      video.setAttribute('height', height);
      canvas.setAttribute('width', width);
      canvas.setAttribute('height', height);
      streaming = true;
    }
  }, false);

	startbutton.addEventListener('click', function(ev){
      takepicture();
    ev.preventDefault();
  }, false);
//au sein de cette fonction que l'on va recuperer l'image.
	function takepicture() {
    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(video, 0, 0, width, height);
    var data = canvas.toDataURL('image/png');

    photo.setAttribute('src', data);
	var canva = document.getElementById('canvas');
	var sender = canva.toDataURL('image/png', 1.0);
	var elem = new XMLHttpRequest();
	var url = "screen_create.php";
	elem.open("POST", url, true);
	elem.setRequestHeader("Content-type", "application/upload");
	//console.log("le truc a pour valeur:[");
	//console.log(truc);
	//console.log("]");
	if (truc != "")
		var sending = [truc, itm, width, height];
	else
		var sending = [sender, itm, width, height];
	//console.log("voici ce que j'envoie");
	//console.log(sending);
	elem.send(sending);

      																																								showImg('test');

}})();

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

function deleteImg(tab){
//			alert(tab);

}
		</script>
	</main>
