<head><meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>Edit Locations</title>
</head>
</body>
<h2>Edit Locations</h2>
<?php
require_once('labels.php');
require_once('DB.php');
require_once('./dsn.php');

$db = DB::connect($dsn);
if (DB::iserror($db)) die(__FILE__ . '.' . __LINE__ . ': ' . $db->getMessage());
$labels = new Labels($db, 'itemmap', 'locations');

require('labelsform.php')
?>
