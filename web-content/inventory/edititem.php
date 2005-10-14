<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
		<title>Edit Item</title>
		<link href="style.css" type="text/css" rel="stylesheet" media="all">
	</head>
<body>
<?php
require_once('util.php');
require_once('labels.php');
require_once('DB.php');
require_once('HTML/QuickForm.php');
require_once('./dsn.php');

// connect
$db = DB::connect($dsn);
if (DB::iserror($db)) die(__FILE__ . '.' . __LINE__ . ': ' . $db->getMessage());

$q_id = $q_deb = 0;
$q_cont = '';	// continuation url
extract($_GET, EXTR_PREFIX_ALL, 'q');
$q_deb > 0 && pre_var_dump($_GET);

$labels = new Labels($db);
$locations = new Labels($db, 'itemmap', 'locations');

$heading = 'Edit Item';
if ($REQUEST_METHOD == 'POST') {
  $q_deb = $_POST['deb'];
  $q_cont = $_POST['cont'];
	$q_deb > 0 && pre_var_dump($_POST);
	$row = $_POST;
} else if ($q_id > 0) {	// build and issue the initial query
	$sql = "SELECT id,name,description,quantity,
			manufacturer,model,sn,retailer,
			purchased,price,value,url,dimensions,artist,url,notes,year
		FROM items
		WHERE id=$q_id";
	$q_deb > 0 && pre_var_dump($sql);
	$q = $db->query($sql);
	if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
	if ($q->numRows() == 1) {
		$status = $q->fetchInto($row, DB_FETCHMODE_ASSOC);
		if (DB::iserror($status)) die(__FILE__ . '.' . __LINE__ . ': ' . $status->getMessage());
	} else
		$q_id = 0;  // id not found; treat as Add request
}
if ($q_id == 0) {	// Add request
		$row = array('id' => 0, 'name' => 'Name is required');
		$heading = 'Add Item';
}

function Update(&$values, &$sql, &$vals) {
	global $db, $q_deb;
	$sql = 'UPDATE items ' . $sql . ' WHERE id=' . $values['id'];
	if ($q_deb > 0) { pre_var_dump($values,'Update'); pre_var_dump($sql); pre_var_dump($vals); }
	$q = $db->query($sql, $vals);
	if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
}

function Add(&$values, &$sql, &$vals) {
	global $db, $q_deb;
	$sql = 'INSERT items ' . $sql;
	if ($q_deb > 0) { pre_var_dump($values,'Add'); pre_var_dump($sql); pre_var_dump($vals); }
	$q = $db->query($sql, $vals);
	if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
	$values['id'] = $db->getone('SELECT LAST_INSERT_ID()');
	$q_deb > 0 && pre_var_dump($values['id'],'New Id');
}

function &buildForm(&$values) {
	global $q_deb, $q_cont, $locations, $labels;
	$q_deb > 0 && pre_var_dump($values,'buildForm $values');
	$form = new HTML_QuickForm('Item');
	$attrs = array('size' => 45, 'maxlength' => 255);
	$form->addElement('text', 'id', 'Id', $attrs);
	foreach (explode(',', 'name,description,
			quantity,manufacturer,model,sn,retailer,
			price,value,url,dimensions,artist,year') as $k)
		$form->addElement('text', trim($k), ucfirst(trim($k)), $attrs);
	$form->addElement('textarea', 'notes', 'Notes', array('rows' => 3, 'cols' => 44));
	$form->addElement('date', 'purchased', 'Purchased',
			array('format' => 'Ymd', 'minYear' => 1986, 'addEmptyOption' => True));
	$id = $values['id'];
	$form->setDefaults($values);
	$e =& $form->addElement('select', 'location', 'Location', $locations->getLabels());
	if ($id > 0)
		$e->setSelected(array_keys($locations->getLabelsForItemId($id)));
	$e =& $form->addElement('select', 'labels', 'Labels', $labels->getLabels(), array('multiple'));
	if ($id > 0)
		$e->setSelected(array_keys($labels->getLabelsForItemId($id)));
	else {
		$e->setSelected(array($locations->getLabelId('Redmond:')));
		$e =& $form->getElement('id');
		$e->setValue(NULL);
	}
	if ($id > 0)
		$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Update');
	$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Add');
	$form->addGroup($buttons);
	$form->addElement('hidden', 'deb', $q_deb);
	$form->addElement('hidden', 'cont', $q_cont);
	$form->applyFilter('__All__', 'trim');
	$form->addRule('name', 'Name is required', 'required');
	$form->addRule('quantity', 'Quantity must be positive', 'regex', '/^[1-9]\d*$/');
	$form->addRule('price', 'Price must be positive', 'regex', '/^[1-9]\d*(\.\d\d)?$/');
	$form->addRule('value', 'Value must be positive', 'regex', '/^[1-9]\d*(\.\d\d)?$/');
	$form->addRule('location', 'Location is required', 'required');
	$form->addRule('year', 'Year must be YYYY', 'regex', '/^(19|20)\d\d$/');
	$e =& $form->getElement('id');
	$e->freeze();
	return $form;
	}

function processData(&$values) {
	global $db, $labels, $locations;
	$sql = 'SET name=?';
	$vals = array($values['name']);
	foreach (array('description','quantity','manufacturer','model','sn','price',
								 'retailer', 'url','dimensions','artist','year','notes') as $k) {
		$sql .= ",$k=?";
		$vals[] = empty($values[$k]) ? NULL : $values[$k];
	}
  extract($values['purchased'], EXTR_PREFIX_ALL, 'date');
  if (!empty($date_Y) && !empty($date_m) && !empty($date_d)) {
  	$sql .= ',purchased=?';
  	$vals[] = $date_Y . '-' . $date_m . '-' . $date_d;
  }
	$values['submit']($values, $sql, $vals);
	$id = $values['id'];
	$labels->updateLabelsForItemId($id, $values['labels']);
	$_location = array($values['location']);
	$locations->updateLabelsForItemId($id, $_location);
}

$form =& buildForm($row);
if ($form->validate()) {
	$row =& $form->getSubmitValues();
	processData($row);
	$_POST = NULL;
	unset($row['submit']);
	$form =& buildForm($row);
	$heading = 'Edit Item';
}

echo "<h2>$heading</h2>";
if (!empty($q_cont))
	echo '<p>', html_link(urldecode($q_cont), 'Back to main page'), '</p>';
$form->display();
if (!empty($q_cont))
	echo '<p>', html_link(urldecode($q_cont), 'Back to main page'), '</p>';
?>
</body>

</html>
