<!-- edit labels -->
<html>
<head>
	 <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
	 <title>Edit Labels</title>
</head>
<body>
<h2>Edit Labels</h2>
<?
require('util.php');
extract($_GET, EXTR_PREFIX_ALL, '');
require('../models/dsn.php');

require('navigation.php');
require('labelsform.php');
require('navigation.php');
?>
</body>
</html>
