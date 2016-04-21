<?php
  class UnholyFactory {

    private $_tab = array();

    function __construct(){}

    function absorb($soldier) {
      if (get_parent_class($soldier) == 'Fighter')
      {
        if (!array_key_exists($soldier->type, $this->_tab))
        {
          $this->_tab[$soldier->type] = get_class($soldier);
            echo "(Factory absorbed a fighter of type " . "$soldier->type" . ")" . PHP_EOL;
        }
        else
              echo "(Factory already absorbed a fighter of type " . "$soldier->type" . ")" . PHP_EOL;
      }
    else
      echo "(Factory can't absorb this, it's not a fighter)" . PHP_EOL;
    }

    function fabricate($man) {
      if (array_key_exists($man, $this->_tab))
      {
        echo "(Factory fabricates a fighter of type " . $man . ")" . PHP_EOL;
        $new = new $this->_tab[$man];
        return ($new);
      }
      else {
        echo "(Factory hasn't absorbed any fighter of type " . $man . ")" . PHP_EOL;
        return NULL;
      }
    }
  }
 ?>
