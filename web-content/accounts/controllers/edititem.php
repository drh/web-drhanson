<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>Edit Item</title>
<style>
form td { text-align: right }
</style>
</head>
<body>
<?php
require_once('util.php');
require_once('../models/item.php');
require_once('HTML/QuickForm.php');

$_id = 0;
extract($_GET, EXTR_PREFIX_ALL, '');

$items = new Item();
$labelmap = $items->labelmap;
$labels = $items->labels;

function &buildForm($id) {
	global $items, $labels, $labelmap, $_owner, $_label;
	$form = new HTML_QuickForm("Item");
	foreach (array('name' => 35, 'url' => 100, 'number' => 20, 'login' => 25,
								 'password' => 20, 'owner' => 20) as $key => $maxlen) {
		$form->addElement('text', $key, ucfirst($key),
											array('size' => 30, 'maxlength' => $maxlen));
		$form->applyFilter($key, 'trim');
	}
	$labs = array();
	foreach ($labels->find_all() as $i => $item)
		$labs[$item['id']] = $item['label'];
	$e =& $form->addElement('select', 'labels', 'Labels', $labs, array('multiple'));
	if ($id > 0) {
		$labs = array();
		foreach ($labelmap->find(array('item_id' => $id)) as $i => $item)
			$labs[] = (int)$item['label_id'];
		$e->setSelected($labs);
	}
	$form->addElement('textarea', 'notes', 'Notes', array('cols' => 29, 'rows' => 3));
	if ($id > 0)
		$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Update');
	$buttons[] =& HTML_QuickForm::createElement('submit', 'submit', 'Add');
	$form->addGroup($buttons, null);
	$form->addElement('hidden', 'id', -1);
	$form->addElement('hidden', 'owner', $_owner);
	$form->addElement('hidden', 'label', $_label);
	$form->addRule('id', 'Id must be nonzero', 'nonzero');
	$form->addRule('name', 'Name is required', 'required');
	$form->addRule('owner', 'Owner is required', 'required');
	$form->addRule('submit', '', 'regex', '/^(Update|Add)$/');
	if ($id > 0) {
		$item = $items->find(array('id' => $id));
		$form->setDefaults($item[0]);
	}
	return $form;
}

$form =& buildForm($_id);
if ($form->validate()) {
	$values = $form->getSubmitValues();
	if ($values['submit'] == 'Add')
		$item =& $items->create($values);
	elseif ($values['submit'] == 'Update')
		$item =& $items->update($values);
	$_id = $item['id'];
	# update labels
	$params = array('item_id' => $_id);
	$labelmap->delete($params);
	foreach ($values['labels'] as $_ => $label_id) {
		$params['label_id'] = $label_id;
		$labelmap->create($params);
	}
	$_POST = NULL;
	$form =& buildForm($_id);
}
?>
<h2><?= $_id > 0 ? 'Edit' : 'Add' ?> Item</h2>
<?
require('navigation.php');
$form->display();
require('navigation.php');
?>
</body>
</html>
