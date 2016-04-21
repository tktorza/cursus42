<?php
  class Vertex
  {
    private $_x;
    private $_y;
    private $_z;
    private $_w;
    private $_color;
    static public $verbose = FALSE;

    function __construct( array $kwarks ){
        $this->_x = floatval($kwarks['x']);
        $this->_y = floatval($kwarks['y']);
        $this->_z = floatval($kwarks['z']);
        if (array_key_exists('w', $kwarks))
          $this->_w = floatval($kwarks['w']);
        else
          $this->_w = 1.0;
        if (array_key_exists('color', $kwarks))
          $this->_color = $kwarks['color'];
        else
            $this->_color = new Color( array( 'red' => 255, 'green' =>   255, 'blue' =>   255 ) );;
        if (self::$verbose == TRUE)
          echo $this . " constructed\n";
    }

    function __toString(){
        if (self::$verbose == TRUE)
          return (sprintf("Vertex ( x: %.2f, y: %.2f, z:%.2f, w:%.2f, %s )",
            $this->_x, $this->_y, $this->_z, $this->_w, $this->_color));
        else if (self::$verbose == FALSE)
          return (sprintf("Vertex ( x: %.2f, y: %.2f, z:%.2f, w:%.2f )",
            $this->_x, $this->_y, $this->_z, $this->_w));
    }

    function __destruct(){
      if (self::$verbose == TRUE)
        echo $this . ") destructed\n";
    }

    static function doc() {
      return (file_get_contents('Vertex.doc.txt'));
    }
  }
?>
