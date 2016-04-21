<?php
	class Color
	{
		public $red;
		public $green;
		public $blue;
		static public $verbose = FALSE;

		function __construct( array $kwargs ) {
			if (array_key_exists('rgb', $kwargs))
			{
				$this->red = intval($kwargs['rgb'] >> 16) % 256;
				$this->green = intval($kwargs['rgb'] >> 8) % 256;
				$this->blue = intval($kwargs['rgb']) % 256;
			}
			else {
				$this->red = $kwargs['red'];
				$this->green = $kwargs['green'];
				$this->blue = $kwargs['blue'];
			}
			if (self::$verbose == TRUE)
				echo $this . " constructed.\n";
		}

		function __destruct() {
				if (self::$verbose == TRUE)
				echo $this . " destructed\n";
			return;
		}

		static function doc() {
			return (file_get_contents('Color.doc.txt'));
		}

		function __toString()
		{
			return (sprintf("Color( red: %3d, green: %3d, blue: %3d )", $this->red, $this->green, $this->blue));
		}

		function add(Color $toadd){
			return (new Color (array ('red' =>		$this->red + $toadd->red,
																'green' =>	$this->green + $toadd->green,
																'blue' =>		$this->blue + $toadd->blue)));
		}

		function sub(Color $toadd){
			return (new Color (array ('red' =>		$this->red - $toadd->red,
																'green' =>	$this->green - $toadd->green,
																'blue' =>		$this->blue - $toadd->blue)));
		}

		function mult($tomult){
			return (new Color (array ( 	'red' => $this->red * $tomult,
																	'green' => $this->green * $tomult,
																	'blue'  => $this->blue * $tomult)));
		}

	}

//	$instance = new Color();
?>
