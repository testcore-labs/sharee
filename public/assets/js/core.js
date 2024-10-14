var dragger = function() {
  return {
    move: function(divid, xpos, ypos) {
      divid.style.left = xpos + 'px';
      divid.style.top = ypos + 'px';
    },
    startmoving: function(divid, container, evt) {
      document.body.classList.add("userselectnoneall");
      var rect = divid.getBoundingClientRect();
      evt = evt || window.event;
      var offsetX = evt.clientX - rect.left,
        offsetY = evt.clientY - rect.top,
        posX = evt.clientX,
        posY = evt.clientY,
        divTop = divid.style.top,
        divLeft = divid.style.left,
        eWi = parseInt(divid.style.width),
        eHe = parseInt(divid.style.height),
        cSt = document.querySelector(container).style;
        
      if (cSt.position === "" || cSt.position === "static") {
        cSt.position = 'absolute';
      }

      divTop = divTop.replace('px', '');
      divLeft = divLeft.replace('px', '');
      var diffX = posX - divLeft,
        diffY = posY - divTop;
      document.onmousemove = function(evt) {
        rect = divid.getBoundingClientRect();
        evt = evt || window.event;
        var posX = evt.clientX,
          posY = evt.clientY,
          aX = posX - offsetX,
          aY = posY - offsetY;

        cSt.left = aX + 'px';
        cSt.top = aY + 'px';
        dragger.move(divid, 0, 0);
      };
      document.onmouseup = function() {
        dragger.stopmoving(divid, container);
      };
    },
    stopmoving: function(divid, container) {
      document.body.classList.remove("userselectnoneall");
      var containerElement = (container == "next") ? divid.nextSibling : ((container == "previous") ? divid.previousSibling : document.querySelector(container));
      containerElement.style.cursor = 'default';

      document.onmousemove = null;
      document.onmouseup = null;
    },
  };
}();


var CHIMES = new Audio("/assets/snds/CHIMES.WAV");
var CHORD = new Audio("/assets/snds/CHORD.WAV");
var BOOT = new Audio("/assets/snds/BOOT.wav");
//var PINGUZONE = new Audio("/assets/snds/PINGUZONE.mp3");
var PINGUZONE4 = "/assets/snds/PINGUZONE.mp4";
function chime() {
  return CHIMES.play();
}
function chord() {
  return CHORD.play();
}
function boot() {
  return BOOT.play();
}
function zone() {
  if(document.getElementById("pingu")) {
  var video = document.getElementById("pingu");
  } else {
  var video = document.createElement("video");
  video = document.body.appendChild(video);
  video.style.position = "absolute";
  video.style.top = "0";
  video.style.width = "100vw";
  video.style.height = "100vh";
  video.setAttribute("id", "pingu");
  var src = document.createElement("source");  
  src = video.appendChild(src);
  src.setAttribute('src', PINGUZONE4);
  src.setAttribute('type', 'video/mp4');
  video.load();
  }
  document.addEventListener("click", enter)
  document.addEventListener("mouseover", enter)
  document.addEventListener("fullscreenchange", enter);
  document.addEventListener("mozfullscreenchange", enter);
  document.addEventListener("webkitfullscreenchange", enter);
  document.addEventListener("keypress", enter)

  function enter() {
    var canfulscren = true;
    if (document.body.requestFullscreen && canfulscren) {
      document.body.requestFullscreen();
    } else if (document.body.mozRequestFullScreen && canfulscren) {
      document.body.mozRequestFullScreen();
    } else if (document.body.webkitRequestFullscreen && canfulscren) {
      document.body.webkitRequestFullscreen();
    }


    video.addEventListener("ended", function() {
    video.remove();
    canfulscren = false;
    });
    if(!canfulscren) {
    document.removeEventListener("click", arguments.callee)
    document.removeEventListener("mouseover", arguments.callee)
    document.removeEventListener("fullscreenchange", arguments.callee);
    document.removeEventListener("mozfullscreenchange", arguments.callee);
    document.removeEventListener("webkitfullscreenchange", arguments.callee);
    document.removeEventListener("keypress", arguments.callee);
    }
    video.play();
  }
  return video;
}

window.addEventListener('online', function() {
  chime();
  console.log('online');
});
window.addEventListener('offline', function () {
  chord();
  console.log('offline');
});


// https://stackoverflow.com/questions/16941104/remove-a-parameter-to-the-url-with-javascript
function removeParam(key, sourceURL) {
  var rtn = sourceURL.split("?")[0],
      param,
      params_arr = [],
      queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
  if (queryString !== "") {
      params_arr = queryString.split("&");
      for (var i = params_arr.length - 1; i >= 0; i -= 1) {
          param = params_arr[i].split("=")[0];
          if (param === key) {
              params_arr.splice(i, 1);
          }
      }
      if (params_arr.length) rtn = rtn + "?" + params_arr.join("&");
  }
  return rtn;
}