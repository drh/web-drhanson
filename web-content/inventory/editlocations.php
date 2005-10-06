<html>
<head><meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>Edit Locations</title>
</head>
</body>
<h2>Edit Locations</h2>
<?php
require_once('util.php');
require_once('DB.php');
require_once('HTML/QuickForm.php');
require_once('./dsn.php');

// connect
$db = DB::connect($dsn);
if (DB::iserror($db)) die(__FILE__ . '.' . __LINE__ . ': ' . $db->getMessage());

function &buildForm(&$db) {
	global $q_deb, $q_cont;
  $form = new HTML_QuickForm('labels', 'post', '', '_self', NULL, True);
  
  $q = $db->query('SELECT id,house,room FROM locations ORDER BY house,room');
	if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
	while ($status = $q->fetchInto($row, DB_FETCHMODE_OBJECT)) {	
		$group[1] =& HTML_QuickForm::createElement('checkbox', "ids[$row->id]");
		$group[2] =& HTML_QuickForm::createElement('text', "house$row->id", '');
		$group[2]->setValue($row->house);
		$group[3] =& HTML_QuickForm::createElement('text', "room$row->id", '');
		$group[3]->setValue($row->room);
		$form->addGroup($group, null);
	}
	unset($group[3]);
	$group[1] =& HTML_QuickForm::createElement('submit', 'submit', 'Delete');
	$group[2] =& HTML_QuickForm::createElement('submit', 'submit', 'Rename');
	$form->addGroup($group, null);
	$group[1] =& HTML_QuickForm::createElement('text', 'newlabel');
	$group[2] =& HTML_QuickForm::createElement('submit', 'submit', 'Add');
	$form->addGroup($group, null);
	$form->addElement('hidden', 'cont', $q_url);
	$form->addElement('hidden', 'deb', $q_deb);
	$form->applyFilter('__ALL__', 'trim');
	$form->addRule('submit', '', 'regex', '/^(Delete|Rename|Add)$/');
	return $form;
}

$form =& buildForm($db);
$q_deb = 0;
$q_cont = '';	// continuation url
extract($_GET, EXTR_PREFIX_ALL, 'q');
$q_deb > 0 && var_dump($_GET);
if ($REQUEST_METHOD == 'POST') {
	$q_deb > 0 && var_dump($_POST);
	extract($_POST, EXTR_PREFIX_ALL, 'q');
}

$form->display();

if (!empty($q_cont))
	echo html_link(urldecode($q_cont), 'Back to main page');
?>
