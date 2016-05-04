<?php include('users/user_connect.php'); ?>
<main>
		<SPAN id="cover" class="webcam">
			<video id="video"></video>
			<button id="startbutton">Prendre une photo</button>
			<canvas id="canvas"></canvas>
		</span>
		<img src="http://img0.mxstatic.com/wallpapers/b95b28b4e31057e253aac3472c0aed41_large.jpeg" class="other" id="photo" alt="photo">
		<script type="text/javascript">

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

	function takepicture() {
    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(video, 0, 0, width, height);
    var data = canvas.toDataURL('image/png');
    photo.setAttribute('src', data);
  }

})();
		</script>
	</main>
