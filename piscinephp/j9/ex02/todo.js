window.onload = function()
{

  var ca = document.cookie.split(';');
  for(var i=0; i<ca.length; i++) {
      var c = ca[i];
      var x = 0;
      while (c[x] != '=')
        x++;
      x++;
      newi(c.slice(x));
  }
  return "";
}

function Getcookie(name, value, days){
  if (days){
    var date = new Date();
    date.setTime(date.getTime()+(days*24*60*60*1000));
    var expire = "; expire="+date.toGMTString();
  }
  else {
    var expire = "";
  }
  document.cookie = name+"="+value+expire+"; path=/";
}

function newi(toupet){
  if (toupet)
    var todo = toupet;
  else
    var todo = prompt("Please enter a new to do", "Fuck on the air");
  if (todo)
  {
    var div = document.createElement('div');
    div.innerHTML = todo;
    div.id = todo;
    var myctn = document.getElementById('ft_list');
    var div = myctn.insertBefore(div, myctn.firstChild);
    Getcookie(todo, todo, 10000);
      div.addEventListener("click", function(lol)
      {
          var verife = confirm("Are you sure you want to delete this todo?");
          if (verife)
          {
            Getcookie(lol.target.id, "", -1);
             lol.target.remove();
           }
        });
  }
}
