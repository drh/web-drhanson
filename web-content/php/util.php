<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<?php

# returns $array[$index], if it exists, or $default otherwise
function getElement($array, $index, $default=NULL) {
	if (array_key_exists($index, $array))
		$default = $array[$index];
	return $default;	
}

# returns $_GET[$key], it it exists, or $default otherwise
function getGET($key, $default=NULL) {
	return getElement($_GET, $key, $default);
}

# returns $_POST[$key], it it exists, or $default otherwise
function getPOST($key, $default=NULL) {
	return getElement($_POST, $key, $default);
}

# returns $_REQUEST[$key], if it exists, or $default otherwise
function getREQUEST($key, $default=NULL) {
	return getElement($_REQUEST, $key, $default);
}

# returns $val if it's not empty, or $default otherwise
function value($val, $default="") {
		return empty($val) ? $default : $val;
}

// returns <a href="htmlentities($url)">$text</a>
function html_link($url, $text) {
		if (empty($url)) return $text;
		return '<a href="' . htmlentities($url) . '"' . ">$text</a>";
	}

$PHP_SELF = getElement($_SERVER, "SCRIPT_NAME");
$REQUEST_METHOD = getElement($_SERVER, "REQUEST_METHOD");

?>