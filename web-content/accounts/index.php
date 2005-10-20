<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>Accounts</title>
<style>
.shaded { background: lightgrey }
.actions { font-size: small }
.labels { font-size: small; font-family: sans-serif; text-align: right }
</style>
</head>
<body>
<?php
require_once('util.php');
require_once('models/item.php');

$model = new Item();
$query = array();
foreach (array('owner', 'label') as $k)
	if (!empty($_GET[$k]))
		$query[$k] = $_GET[$k];
$items =& $model->find($query, 'name');

// generate the table
if ($_ENV['PHP_ENV'] != 'production')
	echo '<p><small>', join(' ', $model->dsn), '</small></p>';
require('views/list_items.php');
?>
</body>
</html>
