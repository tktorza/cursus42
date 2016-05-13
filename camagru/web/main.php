<?php include('users/user_connect.php'); ?>
<main>
	<table class="item">
		<tr>
			<td colspan="3">
				<i class="title">Please choose your item:</i>
			</td>
		</tr>
		<tr>
			<td colspan="3">
			</td>
		</tr>
	</tr>
	<tr>
		<td colspan="3">
		</td>
	</tr>
</tr>
<tr>
	<td colspan="3">
	</td>
</tr>
		<tr>
			<td class="tall">
				<div onclick="Item('cadre')">
		<div align="center">
	<img src="images/style/tableau.png" class="tall">
</div>
<div align="right">
		<input type="radio" name="option" id="cadre" value="cadre">
</div>
	</div>
	</td>
		<td class="tall">
			<div onclick="Item('lapin')">
			<img src="images/style/oreille-lapin.png" class="tall">
			<input type="radio" name="option" value="lapin" id="lapin">
		</div>
		</td>
		<td class="tall">
			<div onclick="Item('mickey')">
			<img src="images/style/mickey.png" class="tall">
			<input type="radio" name="option" id="mickey" value="mickey">
		</div>
		</td>
	</tr>
	</table>
		<SPAN id="cover" class="webcam">
			<video id="video"></video>
			<button id="startbutton">Prendre une photo</button>
			<canvas id="canvas"></canvas>
		</span>
		<img src="http://img0.mxstatic.com/wallpapers/b95b28b4e31057e253aac3472c0aed41_large.jpeg" class="other" id="photo" alt="photo">
		<script type="text/javascript">

		var itm = "";

		function Item(word){
			itm = word;
			console.log(itm);
		}

		(function(){



			var streaming = false,
		      video        = document.querySelector('#video'),
		      cover        = document.querySelector('#cover'),
		      canvas       = document.querySelector('#canvas'),
		      photo        = document.querySelector('#photo'),
		      startbutton  = document.querySelector('#startbutton'),
		      width = 1000,
		      height = 1000;

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
	var sending = [sender, itm];
	elem.send(sending);
	elem.onreadystatechange = function() {
  if (elem.readyState == 4 && elem.status == 200) {
		console.log(elem.responseText);
}
};
}})();
		</script>
	</main>
