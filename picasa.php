<?php
$title = str_replace("\\'", "'", $_GET['title']);
$album = $_GET['album'];
$url = $_GET['url'];
if (!$album)
	$album = str_replace(' ', '', $title);
if (!$url)
	$url = 'https://picasaweb.google.com/108769365103158008281/' . $album . '?noredirect=1';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?= $title ?></title>
<script type="text/javascript">
function doLoad() {
	window.location.assign("<?= $url ?>");
}
</script>
</head>

<body onload='doLoad()'>
</body>
</html>
