<html>

<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>Accounts</title>
<style>
form td { text-align: right }
.shaded { background: lightgrey }
.actions { font-size: small }
.labels { font-size: small; font-family: sans-serif; text-align: right }
</style>
</head>
<body>
<?php
require_once('util.php');
require_once('labels.php');
require_once('DB.php');
require_once('HTML/Table.php');
require_once('HTML/QuickForm.php');
require_once('./dsn.php');

// connect, fetch labels
$db = DB::connect($dsn);
if (DB::iserror($db)) die(__FILE__ . '.' . __LINE__ . ': ' . $db->getMessage());
$labels = new Labels($db);

$q_id = $q_deb = 0;
$q_submit = $q_action = $q_owner = $q_label = null;	
extract($_GET, EXTR_PREFIX_ALL, 'q');

$form = new HTML_QuickForm("Item");
foreach (array('name' => 35, 'url' => 100, 'number' => 20, 'login' => 25,
							 'password' => 20, 'owner' => 20) as $key => $maxlen) {
	$form->addElement('text', $key, ucfirst($key),
										array('size' => 30, 'maxlength' => $maxlen));
	$form->applyFilter($key, 'trim');
}
$e =& $form->addElement('select', 'labels', 'Labels', $labels->getLabels(), array('multiple'));
if ($q_id > 0)
	$e->setSelected(array_keys($labels->getLabelsForItemId($q_id)));
$form->addElement('textarea', 'notes', 'Notes', array("cols" => 29, "rows" => 3));
$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Update');
$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Add');
$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Cancel');
$form->addGroup($buttons, null);
$form->addElement('hidden', 'id', -1);
$form->addElement('hidden', 'deb', $q_deb);
$form->addRule('id', 'Id must be nonzero', 'nonzero');
$form->addRule('name', 'Name is required', 'required');
$form->addRule('owner', 'Owner is required', 'required');
$form->addRule('submit', '', 'regex', '/^(Update|Add|Cancel)$/');

function processData(&$values) {
	global $db, $labels;
	extract($values, EXTR_PREFIX_ALL, '');
	$sql = $_submit($values);
	$q = $db->query($sql, array($_name, $_number, $_owner, $_login,
															$_password, $_url, $_notes));
	if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
	if (empty($_labels))
		$_labels = array();
	$labels->updateLabelsForItemId($_id, $_labels);
}

function Add(&$values) {
	return 'INSERT items
					SET name=?,number=?,owner=?,login=?,password=?,url=?,notes=?';
}

function Update(&$values) {
  return 'UPDATE items
					SET name=?,number=?,owner=?,login=?,password=?,url=?,notes=?
					WHERE id=' . $values['id'];
}


if ($REQUEST_METHOD == 'POST') {
	$q_deb = $form->exportValue('deb');
	$q_deb > 0 && var_dump($_POST);
	if (getPOST('submit', $_POST) == 'Cancel')
		;
	else if ($form->validate())
		$form->process('processData', false);
	else {
		$q_id = $form->exportValue('id');
		$q_action = 'edit';
	}
}

if ($q_action == "delete" && $q_id > 0) {		// handle delete commands
	$q = $db->query("DELETE items FROM items WHERE id=$q_id");
	if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
	$labels->deleteItemId($q_id);
	$q_action = NULL;
}

// issue the queries
$vals = array();
$sql = 'SELECT id,name,name,login,password,url,owner,notes';
if (!empty($q_label)) {
	$sql .= ',labelmap.labelId
		FROM items,labelmap WHERE labelmap.itemId=id AND labelmap.labelId=?';	
	$vals[] = $labels->getLabelId($q_label, 0);
} else	 
	$sql = 'SELECT id,name,number,login,password,url,owner,notes
					FROM items WHERE 1=1';
if (!empty($q_owner)) {
	$sql .= ' AND owner=?';
	$vals[] = $q_owner;
}
$sql .= ' ORDER BY name ASC';
$q = $db->query($sql, $vals);
if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());

function edit($i, &$row, &$table) {
	global $form;
	$form->setDefaults($row);
	$table->setCellContents($i, 0, $form->toHTML());
	$table->setCellAttributes($i, 0, array('colspan' => 6));		
}

function showpw($i, &$row, &$table) {
	global $PHP_SELF, $q_owner, $q_label;
	fillRow($i, $row, $table);
	$table->setCellContents($i, 2, html_link("$PHP_SELF?label=$q_label&owner=$q_owner", $row["password"]));		
}

function fillRow($i, &$row, &$table) {
	global $PHP_SELF, $labels, $q_label, $q_owner;
	extract($row, EXTR_PREFIX_ALL, '');	
	if (!empty($_url) && !array_key_exists("scheme", parse_url($_url)))
		$_url = "http://$_url";
	$_names = explode(' ', $_owner);
	$table->setCellContents($i, 0, html_link($_url, $_name));
	if (!empty($_notes))
		$table->setCellAttributes($i, 0, array("title" => $_notes));
	$table->setCellContents($i, 1, $_login);
	$table->setCellContents($i, 3, html_link("$PHP_SELF?owner=$_owner&label=$q_label", $_names[0]));
	$table->setCellContents($i, 4, value($_number));
	$table->setCellContents($i, 2,
		empty($_password) ? '' : html_link("$PHP_SELF?id=$_id&action=showpw&label=$q_label&owner=$q_owner", 'show'));
	$table->setCellContents($i, 5, html_link("$PHP_SELF?id=$_id&action=edit&label=$q_label&owner=$q_owner",	 'Edit') . ' ' .
																 html_link("$PHP_SELF?id=$_id&action=delete&label=$q_label&owner=$q_owner", 'Del'));
	$table->setCellAttributes($i, 5, array('class' => 'actions'));
	$str = '';
	foreach ($labels->getLabelsForItemId($_id) as $id => $lab)
		if ($lab != $q_label)
			$str .= ' ' . html_link("$PHP_SELF?label=$lab&owner=$q_owner", $lab);
	$table->setCellContents($i, 6, $str);
	$table->setCellAttributes($i, 6, array('class' => 'labels'));
}

// generate the table
$table = new HTML_Table(array("border" => 0, "rules" => "groups"));
$table -> setAutoGrow(true);
$table -> setAutoFill("");
$table->setHeaderContents(0, 0, "Name");
$table->setHeaderContents(0, 1, "Login");
$table->setHeaderContents(0, 2, "Password");
$table->setHeaderContents(0, 3, "Owner");
$table->setHeaderContents(0, 4, "Number");
$table->setCellContents(0, 6, html_link('/accounts/editlabels.php?url=' . urlencode("$PHP_SELF?owner=$q_owner&label=$q_label"), 'Edit Labels'));
$table->setCellAttributes(0, 6, array('align' => 'right'));
$table->setCellContents(1, 0, html_link("$PHP_SELF", 'All'));
$table->setCellContents(1, 3, html_link("$PHP_SELF?owner=&label=$q_label", 'All'));
$table->setCellContents(1, 6, html_link("$PHP_SELF?owner=$q_owner&label=", 'All'));
$table->setCellAttributes(1, 6, array('align' => 'right'));
$i = 2;
while ($status = $q->fetchInto($row, DB_FETCHMODE_ASSOC)) {
	if (DB::iserror($status)) die(__FILE__ . '.' . __LINE__ . ': ' . $status->getMessage());
	if ($q_action && $row["id"] == $q_id)
		$q_action($i, $row, $table);
	else
		fillRow($i, $row, $table);
	$i++;
}
$table->altRowAttributes(0, null, array("class" => "shaded"), true);
echo $table->toHTML();
?>
</body>
</html>
