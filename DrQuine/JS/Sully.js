const { exec } = require('child_process');
var i = 5;
function ok(){
    i--;
    var fs = require('fs');
    var name = "Sully_"+i+".js";
    if (i >= 0)
       {
    fs.writeFile(name, "const { exec } = require('child_process');\nvar i = "+i+";\n"+ok.toString()+"ok();");
    exec("node "+name);
}        
}ok();