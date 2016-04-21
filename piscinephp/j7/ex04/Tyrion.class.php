<?php
  class Tyrion {
      function sleepWith($thing) {
        if ($thing instanceof Jaime)
          print("Not even if I'm drunk !\n");
        if ($thing instanceof Sansa)
          print("Let's do this.\n");
        if ($thing instanceof Cersei)
          print("Not even if I'm drunk !\n");
      }
  }
 ?>
