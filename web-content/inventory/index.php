<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>Inventory</title>
<link href="style.css" type="text/css" rel="stylesheet" media="all">
</head>

<body>
<?php
require_once('util.php');
require_once('labels.php');
require_once('DB.php');
require_once('HTML/Table.php');
require_once('HTML/QuickForm.php');
require_once('./dsn.php');

// connect
$db = DB::connect($dsn);
if (DB::iserror($db)) die(__FILE__ . '.' . __LINE__ . ': ' . $db->getMessage());

function &buildForm() {
	$form = new HTML_QuickForm('Search', 'get');
	foreach (array('house', 'room', 'label') as $key) {
		$group[] =& HTML_QuickForm::createElement('static', NULL, NULL, ucfirst($key) . ':');
		$group[] =& HTML_QuickForm::createElement('text', $key, ucfirst($key),
											array('size' => 20, 'maxlength' => 20));
	}
	$group[] =& HTML_QuickForm::createElement('submit', 'submit', 'Search');
	$group[] =& HTML_QuickForm::createElement('submit', 'submit', 'Clear');
	$form->addGroup($group);
	$form->addElement('hidden', 'id', 0);
	$form->addElement('hidden', 'deb', 0);
	$form->applyFilter('__ALL__', 'trim');
	$form->addRule('submit', '', 'regex', '/^(Search|Clear)$/');
	return $form;
}

$labels = new Labels($db);
$locations = new Labels($db, 'itemmap', 'locations');
$q_id = $q_deb = 0;
$q_submit = $q_action = $q_house = $q_room = $q_label = null;
$form =& buildForm();
if ($form->validate()) {
	extract($form->getSubmitValues(), EXTR_PREFIX_ALL, 'q');
	$q_deb > 0 && var_dump($_GET);
	if ($q_submit == 'Clear') {
		$_GET = NULL;
		$form =& buildForm();
	}
}

echo html_link('/inventory/editlabels.php?url='
	. urlencode("$PHP_SELF?house=$q_house&room=$q_room&label=$q_label"), 'Edit Labels'),
	'&nbsp;&nbsp;',
	html_link('/inventory/editlocations.php?url='
	. urlencode("$PHP_SELF?house=$q_house&room=$q_room&label=$q_label"), 'Edit Locations');
$form->display();

if ($q_action == 'delete' && $q_id > 0) {
	$q = $db->query("DELETE items FROM items WHERE items.id=$q_id");
	if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
	$labels->deleteItemId($q_id);
	$locations->deleteItemId($q_id);
}

// build and issue the query
$vals = array();
$sql = 'SELECT items.id,name,description,quantity,
		locations.label as location,manufacturer,model,sn,retailer,
		purchased,price,url,dimensions,artist,year,url,notes
	FROM items, itemmap, locations';
if (!empty($q_label)) {
	$sql .= ', labelmap
		WHERE items.id=labelmap.itemId AND labelmap.labelId=?';
	$vals[] = $labels->getLabelId($q_label, 0);
} else
	$sql .= ' WHERE 1=1';	
$sql .= ' AND items.id=itemmap.itemId AND itemmap.labelId=locations.id';
if (!empty($q_house)) {
	$sql .= ' AND UCASE(locations.label) LIKE UCASE(?)';
	$vals[] = $q_house . ':%';
}
if (!empty($q_room)) {
	$sql .= ' AND UCASE(locations.label) LIKE UCASE(?)';
	$vals[] = '%:' . $q_room;
}
$sql .= ' ORDER BY name ASC';
$q_deb > 0 && var_dump($sql);
$q_deb > 0 && var_dump($vals);
$q = $db->query($sql, $vals);
if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());

// generate the tables
$url = urlencode("$PHP_SELF?house=$q_house&room=$q_room&label=$q_label");
echo $q->numRows(), ' items', '&nbsp;&nbsp',
	html_link('edititem.php?cont=' . $url, 'Add New Item'), '<hr>';
$attrs = array('class' => 'label', 'valign' => 'top');
while ($status = $q->fetchInto($record, DB_FETCHMODE_ASSOC)) {
	if (DB::iserror($status)) die(__FILE__ . '.' . __LINE__ . ': ' . $status->getMessage());
	$table = new HTML_Table(array('border' => 0));
	$table->setAutoGrow(true);
	$table->setAutoFill('');
/*
       0               1                 2             3
 0 Id:   Edit Delete
 1        Name:                    Manufacturer:
 2 Description:                           Model:
 3    Quantity:                              Sn:
 4    Location:                      Dimensions:
 5         URL:                          
 6
 7    Retailer:                           Price:
 8   Purchased:
 9
10      Artist:                            Year:
11
12       Notes:                          Labels:
*/
	extract($record, EXTR_PREFIX_ALL, '');
	$table->setCellContents(0, 0, 'ID: ' . $_id . '&nbsp;&nbsp;<span class="actions">' .
		html_link('edititem.php?id=' . $_id . '&cont=' . $url, 'Edit') . ' ' .
		html_link("$PHP_SELF?id=$_id&action=delete&house=$q_house" .
							"&room=$q_room&label=$q_label", 'Delete') . '</span>');
	$table->setCellAttributes(0, 0, array('colspan' => 2));
	$table->setCellAttributes(1, 1, array('style' => 'width: 20em'));

	$col = 0;
  foreach (array('Name' => 1, 'Description' => 2, 'Quantity' => 3, 'Location' => 4,
								 'URL' => 5, 'Retailer' => 7, 'Purchased' => 8, 'Artist' => 10,
								 'Notes' => 12) as $lab => $row) {
		$table->setCellContents($row, $col, $lab . ':&nbsp;');
		$table->setCellAttributes($row, $col, $attrs);
		$table->setCellContents($row, $col + 1, $record[strtolower($lab)]);
		$table->setCellAttributes($row, $col + 1, array('style' => 'width: 20em'));
	}
	if (!empty($_url)) {
		if (!array_key_exists('scheme', parse_url($_url)))
			$_url = "http://$_url";
		$table->setCellContents(5, $col + 1, html_link($_url, $record['url']));
	}
	$table->setCellAttributes(5, $col + 1, array('colspan' => 3));
	$loc = explode(':', $_location, 2);
	$v = html_link("$PHP_SELF?house=$loc[0]&label=$q_label&room=$q_room", $loc[0]);
	if ($loc[1])
		$v .= ':' . html_link("$PHP_SELF?house=$q_house&label=$q_label&room=$loc[1]", $loc[1]);
	$table->setCellContents(4, $col + 1, $v);

	$col = 2;
  foreach (array('Manufacturer' => 1, 'Model' => 2, 'SN' => 3, 'Dimensions' => 4,
								 'Price' => 7, 'Year' => 10, 'Labels' => 12) as $lab => $row) {
		$table->setCellContents($row, $col, $lab . ':');
		$table->setCellAttributes($row, $col, $attrs);
		$table->setCellContents($row, $col + 1, $record[strtolower($lab)]);
	}
	$v = '';
	foreach ($labels->getLabelsForItemId($_id) as $lab)
		if ($lab != $q_label)
			$v .= ' ' . html_link("$PHP_SELF?label=$lab&room=$q_room&house=$q_house", $lab);
	$table->setCellContents(12, $col + 1, $v);
	echo $table->display();
	echo "<hr>\n";
}
?>
</body>
</html>
