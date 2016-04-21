<?php
  class Targaryen{

    protected function resistsFire(){
      return FALSE;
    }

    public function getBurned()
    {
      if ($this->resistsFire())
        return("emerges naked but unharmed");
      else
        return("burns alive");
    }
  }
 ?>
