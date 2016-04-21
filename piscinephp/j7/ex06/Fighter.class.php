<?php
  abstract class Fighter {
    public $type;

    function __construct($type){
      $this->type = $type;
    }

    abstract function fight($target);
  }
 ?>
