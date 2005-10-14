<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>Inventory</title>
<link href="style.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript">
function flip(id,linkid) {
	elem = document.getElementById(id);
	link = document.getElementById(linkid);
	if (elem.style.display == "none") {
		link.src = "images/editing.gif";
		elem.style.display = "table-row";
	} else {
		link.src = "images/edit.gif";
		elem.style.display = "none";
	}
}
</script>
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

$url = 	urlencode("$PHP_SELF?house=$q_house&room=$q_room&label=$q_label");
echo html_link('/inventory/editlabels.php?url=' . $url, 'Edit Labels'),	'&nbsp;&nbsp;',
	html_link('/inventory/editlocations.php?url=' . $url, 'Edit Locations');
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
		purchased,price,value,url,dimensions,artist,year,url,notes
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
$table = new HTML_Table(array('border' => 0));
$table->setAutoGrow(true);
$table->setAutoFill('');
$table->setHeaderContents(0, 1, 'Name');
$table->setHeaderContents(0, 2, 'Location');
$table->setHeaderContents(0, 3, 'Labels');
$row = 1;
$attrs = array('class' => 'label', 'valign' => 'top');
while ($status = $q->fetchInto($record, DB_FETCHMODE_ASSOC)) {
	if (DB::iserror($status)) die(__FILE__ . '.' . __LINE__ . ': ' . $status->getMessage());
	extract($record, EXTR_PREFIX_ALL, '');
	// compute values common to both views
	$actions = '<span class="actions">' .
		html_link('edititem.php?id=' . $_id . '&cont=' . $url, 'Edit') . ' ' .
		html_link("$PHP_SELF?id=$_id&action=delete&house=$q_house" .
							"&room=$q_room&label=$q_label", 'Delete') . '</span>';
	if (!empty($_url) && !array_key_exists('scheme', parse_url($_url)))
			$_url = "http://$_url";
	$loc = explode(':', $_location, 2);
	$_location = html_link("$PHP_SELF?house=$loc[0]&label=$q_label&room=$q_room", $loc[0]);
	if ($loc[1])
		$_location .= ':' . html_link("$PHP_SELF?house=$q_house&label=$q_label&room=$loc[1]", $loc[1]);
	$_labels = '';
	foreach ($labels->getLabelsForItemId($_id) as $lab)
		if ($lab != $q_label)
			$_labels .= ' ' . html_link("$PHP_SELF?label=$lab&room=$q_room&house=$q_house", $lab);
	/*
	 + Name Location Labels  Edit Delete
	*/
	$table->setCellContents($row, 0, "<img id='a$_id' onclick=\"flip('tr$_id','a$_id');\" src='images/edit.gif'>");
	if (empty($_url))
		$table->setCellContents($row, 1, $_name);
	else
		$table->setCellContents($row, 1, html_link($_url, $_name));
	if (!empty($_description))
		$table->setCellAttributes($row, 1, array('title' => htmlentities($_description)));
	$table->setCellContents($row, 2, $_location);
	$table->setCellContents($row, 3, $_labels);
	$table->setCellContents($row, 4, $actions);
	if ((($row-1)/2)%2 == 0)
		$table->setRowAttributes($row, array('class' => 'shaded'), True);

	$detail =& detailTable($record);
	$table->setCellContents($row + 1, 0, $detail->toHTML());
	$table->setCellAttributes($row + 1, 0, array('colspan' => 4));
	$table->setRowAttributes($row + 1,
		array('id' => 'tr' . $_id, 'style' => 'display: none'), True);
	$row += 2;
}
$table->display();

function &detailTable($record) {
	global $_labels, $_location, $_url, $attrs, $_id;
	$detail = new HTML_Table();
	$detail->setAutoGrow(true);
	$detail->setAutoFill('');
	/*
				 0               1                 2             3
	 0          Id:
	 1 Description:                    Manufacturer:
 	 2    Quantity:                           Model:
	 3  Dimensions:                              Sn:
	 4         URL:                          
	 5
	 6    Retailer:                           Price:
	 7   Purchased:                           Value:
	 8
	 9      Artist:                            Year:
	10       Notes:
	*/
	$detail->setCellAttributes(1, 1, array('style' => 'width: 20em'));
	$col = 0;
	foreach (array('ID' => 0, 'Description' => 1, 'Quantity' => 2, 'URL' => 4,
								 'Dimensions' => 3, 'Retailer' => 6, 'Purchased' => 7,
								 'Artist' => 9, 'Notes' => 10) as $lab => $row) {
		$detail->setCellContents($row, $col, $lab . ':&nbsp;');
		$detail->setCellAttributes($row, $col, $attrs);
		$detail->setCellContents($row, $col + 1, $record[strtolower($lab)]);
		$detail->setCellAttributes($row, $col + 1, array('style' => 'width: 20em'));
	}
	if (!empty($_url)) {
		$detail->setCellContents(4, $col + 1, html_link($_url, $record['url']));
		$detail->setCellAttributes(4, $col + 1, array('colspan' => 3));
	}

	$col = 2;
	foreach (array('Manufacturer' => 1, 'Model' => 2, 'SN' => 3, 'Price' => 6,
								 'Value' => 7, 'Year' => 9) as $lab => $row) {
		$detail->setCellContents($row, $col, $lab . ':');
		$detail->setCellAttributes($row, $col, $attrs);
		$detail->setCellContents($row, $col + 1, $record[strtolower($lab)]);
	}
	return $detail;
}
?>
</body>
</html>
