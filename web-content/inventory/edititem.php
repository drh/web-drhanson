<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
		<title>Edit Item</title>
		<link href="style.css" type="text/css" rel="stylesheet" media="all">
	</head>
<body>
<?php
require_once("util.php");
require_once("DB.php");
require_once("HTML/QuickForm.php");
require_once("./dsn.php");

// connect
$db = DB::connect($dsn);
if (DB::iserror($db)) die(__FILE__ . '.' . __LINE__ . ': ' . $db->getMessage());

$q_id = $q_deb = 0;
extract($_GET, EXTR_PREFIX_ALL, 'q');
$q_deb > 0 && var_dump($_GET);

// build and issue the query
if ($q_id > 0) {
  $sql = "SELECT id,name,description,
    quantity,manufacturer,model,sn,retailer,
    purchased,price,url,dimensions,artist,url,notes
  FROM items
  WHERE items.id=$q_id";
  $q_deb > 0 && var_dump($sql);
  $q = $db->query($sql);
  if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
  if ($q->numRows() == 1) {
    $status = $q->fetchInto($row, DB_FETCHMODE_ASSOC);
    if (DB::iserror($status)) die(__FILE__ . '.' . __LINE__ . ': ' . $status->getMessage());
  } else
    $q_id = 0;  // id not found; treat as Add request
}

$form = new HTML_QuickForm('Item');
$attrs =   array('size' => 45, 'maxlength' => 255);
foreach (explode(',', 'id,name,description,
    quantity,manufacturer,model,sn,retailer,
    purchased,price,url,dimensions,artist,url,notes') as $k)
  $form->addElement('text', trim($k), ucfirst(trim($k)), $attrs);
if ($q_id > 0)
  $form->setDefaults($row);
$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Update');
$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Add');
$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Cancel');
$form->addGroup($buttons, null);
$form->addElement('hidden', 'id', 0);
$form->applyFilter('__All__', 'trim');
$form->display();
?>
	</body>

</html>