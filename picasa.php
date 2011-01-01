<?php
$title = str_replace("\\'", "'", $_GET['title']);
$album = $_GET['album'];
$url = $_GET['url'];
if (!$album)
	$album = str_replace(' ', '', $title);
if (!$url)
	$url = 'http://picasaweb.google.com/drhanson/' . $album;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?= $title ?></title>
<script type="text/javascript">
function adjustIFrameSize(id) {
	var f = document.getElementById(id);
	if (f) {
		var h = window.innerHeight;  // FF
		if (document.documentElement.clientHeight)
			h = document.documentElement.clientHeight; // IE
		f.height = h - f.offsetTop - 
			parseInt(document.getElementById('navbar').style.height) - 8;
	}
}
</script>
</head>

<body onload='adjustIFrameSize("album")' onresize='adjustIFrameSize("album")'>
<div id='navbar' style='height: 20px; width: 100%'>
<p align="right"><a href="http://drhanson.net/">Back to drhanson.net</a></p></div>
<iframe id="album" style='margin: 0 0 0 0' onload='adjustIFrameSize("album")' 
	frameborder="0" border="0" scrolling="auto" src="<?= $url ?>" width="100%"></iframe>
</body>
</html>
