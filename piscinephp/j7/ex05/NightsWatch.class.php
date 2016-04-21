<?php
  class NightsWatch {

    private $_tab = array();

    function __construct() {
    }

    function recruit($recrue) {
      if (class_implements($recrue)["IFighter"])
        $this->_tab[] = $recrue;
    }

    function fight() {
      foreach ($this->_tab as $value) {
        if ($value->fight())
          $value->fight();
      }
    }

  }
?>
