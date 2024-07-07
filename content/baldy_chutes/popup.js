function show(src, title) {
    var div = document.getElementById("image");
    div.innerHTML = `<div style="text-align:center">
    <p>${title}</p>
    <img src="${src}">
    <p><span onclick="hide()">Close</p>
</div>`;
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
