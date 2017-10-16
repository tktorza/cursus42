const { exec } = require('child_process');
var i = 5;
function ok(){
    var path = require('path');
    var scriptName = path.basename(__filename);
if (scriptName !== "Sully.js")
{i--;}
    var fs = require('fs');
    var name = "Sully_"+i+".js";
    if (i >= 0)
       {
    fs.writeFile(name, "const { exec } = require('child_process');\nvar i = "+i+";\n"+ok.toString()+"ok();");
    exec("node "+name);
}        
}ok();