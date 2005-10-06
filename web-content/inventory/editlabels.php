<html>
<head><meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>Edit Labels</title>
</head>
</body>
<h2>Edit Labels</h2>
<?php
require_once('labels.php');
require_once('DB.php');
require_once('./dsn.php');

$db = DB::connect($dsn);
if (DB::iserror($db)) die(__FILE__ . '.' . __LINE__ . ': ' . $db->getMessage());
$labels = new Labels($db);

require('labelsform.php')
?>
