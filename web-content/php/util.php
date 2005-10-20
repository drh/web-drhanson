<?php

# dumps var=value inside a <pre> ... </pre> block.
function pre_var_dump(&$value, $var='') {
	echo '<pre>';
	if (!empty($var)) echo $var, '=';
	var_dump($value);
	echo '</pre>';
}

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

# returns <a href="htmlentities($url)">$text</a>
function html_link($url, $text) {
	return link_to($url, $text);
}

# returns $url?query...#anchor
# ?query... and #anchor are omitted if they are equal ''.
function compose_url($url, $query=array(), $anchor='') {
	$qstr = '';
	foreach ($query as $k => $v)
		$qstr .= "&$k=" . urlencode($v);
	if (strlen($qstr) > 0)
		$url .= '?' . substr($qstr, 1);
	if (strlen($anchor) > 0)
		$url .= '#' . $anchor;
	return $url;
}

# returns <a href="htmlentities($url...)" attrs...>$text</a>
function link_to($url, $text, $query=array(), $attrs=array(), $anchor='') {
	if (empty($url))
		return $text;
	$html = 'href="' . htmlentities(compose_url($url, $query, $anchor)) . '"';
	foreach ($attrs as $k => $v)
		$html .= " $k='$v'";
	return '<a ' . $html . ">$text</a>";
}

# redirect to $url; does not return!
function redirect_to($url, $query=array(), $anchor='') {
	header('Location: ' . compose_url($url, $query, $anchor));
	header('Cache-Control: no-cache');
	exit;
}

$PHP_SELF = getElement($_SERVER, "SCRIPT_NAME");
$REQUEST_METHOD = getElement($_SERVER, "REQUEST_METHOD");

?>