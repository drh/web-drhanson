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
	require_once("util.php");
  require_once("DB.php");
  require_once("HTML/Table.php");
	require_once("HTML/QuickForm.php");

	$form = new HTML_QuickForm("Item");
	foreach (array('name' => 35, 'url' => 100, 'number' => 20, 'login' => 25,
								 'password' => 20, 'owner' => 20) as $key => $maxlen) {
		$form->addElement('text', $key, ucfirst($key),
											array('size' => 30, 'maxlength' => $maxlen));
		$form->applyFilter($key, 'trim');
	}
	$form->addElement('textarea', 'notes', 'Notes', array("cols" => 29, "rows" => 3));
	$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Update');
	$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Add');
	$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Cancel');
	$form->addGroup($buttons, null);
	$form->addElement('hidden', 'id', -1);
	$form->addRule('id', 'Id must be nonzero', 'nonzero');
	$form->addRule('name', 'Name is required', 'required');
	$form->addRule('owner', 'Owner is required', 'required');
	$form->addRule('submit', '', 'regex', '/^(Update|Add|Cancel)$/');

	function processData(&$values) {
		global $db;
		extract($values, EXTR_PREFIX_ALL, '');
		$sql = $_submit($values);
		$q = $db->query($sql, array($_name, $_number, $_owner, $_login,
																$_password, $_url, $_notes));
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
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
	
  // connect
  $db = DB::connect('mysql://drh:323-Hpo@db70c.pair.com/drh_accounts');
  if (DB::iserror($db)) die(__FILE__ . '.' . __LINE__ . ': ' . $db->getMessage());

	$id = getGET('id', -1);
	$action =             array_key_exists('showpw', $_GET) ? 'showpw' : NULL;
	$action = !$action && array_key_exists('delete', $_GET) ? 'delete' : $action;
	$action = !$action && array_key_exists('edit',   $_GET) ? 'edit'   : $action;
	
	if ($REQUEST_METHOD == 'POST') {
		if (getPOST('submit', $_POST) == 'Cancel')
			;
		else if ($form->validate())
			$form->process('processData', false);
		else {
			$id = $form->exportValue('id');
			$action = 'edit';
		}
	}

	if ($action == "delete" && $id > 0) {		// handle delete commands
		$q = $db->query("DELETE items, labelmap FROM items, labelmap
										 WHERE items.id = $id AND labelmap.itemId = $id");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
	}

  // issue the queries
	$q = $db->query('SELECT itemId, label
			FROM labelmap, labels
			WHERE labelId=id');
  if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
	$id_to_labels = array();
	while ($status = $q->fetchInto($row, DB_FETCHMODE_ORDERED)) {
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$i = (int)$row[0];
		if (!array_key_exists($i, $id_to_labels))
			$id_to_labels[$i] = array();			
		array_push($id_to_labels[$i], $row[1]);
	}					
								 
  $q = $db->query('SELECT id,name,number,login,
			password,url,owner,notes
		FROM items
		ORDER BY name ASC');
  if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());

	function edit($i, &$row, &$table) {
		global $form;
		$form->setDefaults($row);
		$table->setCellContents($i, 0, $form->toHTML());
		$table->setCellAttributes($i, 0, array('colspan' => 6));		
	}

	function showpw($i, &$row, &$table) {
		global $PHP_SELF;
		fillRow($i, $row, $table);
		$table->setCellContents($i, 2, html_link("$PHP_SELF", $row["password"]));		
	}

	function fillRow($i, &$row, &$table) {
		global $PHP_SELF, $id_to_labels;
		extract($row, EXTR_PREFIX_ALL, '');	
		if (!empty($_url) && !array_key_exists("scheme", parse_url($_url)))
			$_url = "http://$_url";
		$_owner = explode(' ', $_owner);
		$table->setCellContents($i, 0, html_link($_url, $_name));
		if (!empty($_notes))
			$table->setCellAttributes($i, 0, array("title" => $_notes));
		$table->setCellContents($i, 1, $_login);
		$table->setCellContents($i, 3, $_owner[0]);
		$table->setCellContents($i, 4, value($_number));
		$table->setCellContents($i, 2,
			empty($_password) ? "" : html_link("$PHP_SELF?id=$_id&showpw", "show"));
		$table->setCellContents($i, 5, html_link("$PHP_SELF?id=$_id&edit",   "Edit") . " " .
									html_link("$PHP_SELF?id=$_id&delete", "Del"));
		$table->setCellAttributes($i, 5, array("class" => "actions"));
		if (array_key_exists($_id, $id_to_labels)) {
			$table->setCellContents($i, 6, implode(' ', $id_to_labels[$_id]));
			$table->setCellAttributes($i, 6, array("class" => "labels"));
		}
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
	$i = 1;
  while ($status = $q->fetchInto($row, DB_FETCHMODE_ASSOC)) {
		if (DB::iserror($status)) die(__FILE__ . '.' . __LINE__ . ': ' . $status->getMessage());
		if ($action && $row["id"] == $id)
			$action($i, $row, $table);
		else
			fillRow($i, $row, $table);
		$i++;
  }
	$table->altRowAttributes(0, null, array("class" => "shaded"), true);
	echo $table->toHTML();
?>
</body>
</html>