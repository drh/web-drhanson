function show(src, title) {
    var div = document.getElementById("image");
    div.innerHTML = `<p>${title}</br>
    <img src="${src}"></br>
    <span onclick="hide()">Close</p>`;
    var body = document.getElementById("pagebody");
    body.style.visibility = "hidden";
    div.style.visibility = "visible";
  }
  
  function hide() {
    var div = document.getElementById("image");
    var body = document.getElementById("pagebody");
    div.style.visibility = "hidden";
    body.style.visibility = "visible";
  }
