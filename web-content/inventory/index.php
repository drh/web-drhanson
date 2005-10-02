<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>Inventory</title>
<link href="style.css" type="text/css" rel="stylesheet" media="all">
</head>

<body>
<?php
require_once("util.php");
require_once("DB.php");
require_once("HTML/Table.php");
require_once("HTML/QuickForm.php");
require_once('./dsn.php');

// connect
$db = DB::connect($dsn);
if (DB::iserror($db)) die(__FILE__ . '.' . __LINE__ . ': ' . $db->getMessage());

$search_form = new HTML_QuickForm('Search', 'get');
foreach (array('house', 'room', 'label') as $key) {
	$group[] =& HTML_QuickForm::createElement('text', $key, ucfirst($key),
										array('size' => 20, 'maxlength' => 20));
}
$group[] =& HTML_QuickForm::createElement('submit', 'submit', 'Search');
$group[] =& HTML_QuickForm::createElement('submit', 'submit', 'Clear');
$search_form->addGroup($group);
$search_form->addElement('hidden', 'id', 0);
$search_form->addElement('hidden', 'deb', 0);
$search_form->applyFilter('__ALL__', 'trim');
$search_form->addRule('submit', '', 'regex', '/^(Search|Clear)$/');

$q_id = $q_deb = 0;
$q_submit = $q_action = $q_house = $q_room = $q_label = null;	
extract($_GET, EXTR_PREFIX_ALL, 'q');

if ($q_submit == 'Clear')
	$search_form->setDefaults(array('__ALL__' => null));
if ($q_deb > 0)
	$search_form->setDefaults(array('deb' => $q_deb));
$search_form->display();
$q_deb > 0 && var_dump($_GET);

if ($q_action == 'delete' && $q_id > 0) {
	$sql = "DELETE items, labelmap, itemmap
					FROM items, labelmap, itemmap
					WHERE items.id=$q_id AND labelmap.itemId=$q_id
						AND itemmap.itemId=$q_id";
	$q_deb > 0 && var_dump($sql);
	$q = $db->query($sql);
	if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
	$q_action = NULL;
}

// build and issue the query
$sql = 'SELECT items.id,name,description,itemmap.quantity,
		locations.house,locations.room,manufacturer,model,sn,retailer,
		purchased,price,url,dimensions,artist,url,notes
	FROM items, itemmap, locations
	WHERE items.id=itemmap.itemId and itemmap.roomId=locations.id';
$vals = array();
if (!empty($q_house)) {
	$sql .= ' and locations.house=?';
	$vals[] = $q_house;
}
if (!empty($q_room)) {
	$sql .= ' and locations.room=?';
	$vals[] = $q_room;
}
if (!empty($q_label)) {
	$sql .= ' and labels.label=?';
	$vals[] = $q_label;
}
$sql .= 
	' ORDER BY name ASC';
$q_deb > 0 && var_dump($sql);
$q_deb > 0 && var_dump($vals);
$q = $db->query($sql, $vals);
if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());

// generate the tables
echo $q->numRows() . ' items<hr>';
while ($status = $q->fetchInto($row, DB_FETCHMODE_ASSOC)) {
	if (DB::iserror($status)) die(__FILE__ . '.' . __LINE__ . ': ' . $status->getMessage());
	$table = new HTML_Table(array("border" => 0, "rules" => "groups"));
	$table -> setAutoGrow(true);
	$table -> setAutoFill("");
	$table->setCellContents(0, 0,
		html_link("edititem.php?id="	 . $row['id'], "Edit") . ' ' .
		html_link("$PHP_SELF?action=delete&id=" . $row['id'], "Delete"));
	$table->setCellAttributes(0, 0, array("class" => "actions"));
	$attrs = array("class" => "label");
	$i = 1;
	foreach ($row as $k => $v) {
		$table->setCellContents($i, 0, ucfirst($k) . ':&nbsp;');
		$table->setCellAttributes($i, 0, $attrs);
		if (!empty($v))
			$table->setCellContents($i, 1, $v);
		$i++;
	}
	echo $table->toHTML();
	echo "<hr>\n";
}
?>
</body>
</html>
