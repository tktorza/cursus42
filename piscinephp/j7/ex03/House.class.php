<?php
  class House {
    public function introduce() {
      $truc = $this->getHouseMotto();
      echo "House " . $this->getHouseName() . " of " . $this->getHouseSeat() .
      " : " . '"' . $truc . '"' . PHP_EOL;
    }
  }
 ?>
