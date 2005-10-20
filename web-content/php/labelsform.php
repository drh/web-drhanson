<?php
require_once('util.php');
require_once('label.php');
require_once('labelmap.php');
require_once('HTML/QuickForm.php');

$q_deb = 0;
extract($_GET, EXTR_PREFIX_ALL, 'q');

$labels = new Label($dsn, 'labels');
$labelmap = new LabelMap($dsn, 'labels', 'labelmap');

function &buildForm() {
	global $labels, $labelmap;
	global $q_url, $q_deb, $counts;
	$items =& $labelmap->find_all_counts();
	$form = new HTML_QuickForm('labels', 'post', '', '_self', NULL, True);
	foreach ($items as $i => $row) {
		extract($row, EXTR_PREFIX_ALL, '');
		$group[1] =& HTML_QuickForm::createElement('checkbox', "ids[$_id]");
		$group[2] =& HTML_QuickForm::createElement('text', $_id, '');
		$group[2]->setValue($_label);
		$counts[$_id] = $_item_count;
		$group[3] =& HTML_QuickForm::createElement('static', '', '',
			'<small>(' . $_item_count . ' items)</small>');
		$group[4] =& HTML_QuickForm::createElement('hidden', "count[$_id]", $_item_count);
		$form->addGroup($group, null);
	}
	unset($group[3]);
	unset($group[4]);
	$group[1] =& HTML_QuickForm::createElement('submit', 'submit', 'Delete');
	$group[2] =& HTML_QuickForm::createElement('submit', 'submit', 'Rename');
	$form->addGroup($group, null);
	$group[1] =& HTML_QuickForm::createElement('text', 'newlabel');
	$group[2] =& HTML_QuickForm::createElement('submit', 'submit', 'Add');
	$form->addGroup($group, null);
	$form->addElement('hidden', 'url', $q_url);
	$form->addElement('hidden', 'deb', $q_deb);
	$form->applyFilter('__ALL__', 'trim');
	$form->addRule('submit', '', 'regex', '/^(Delete|Rename|Add)$/');
	return $form;
}

$form =& buildForm();
if ($form->validate()) {
	$values = $form->getSubmitValues();
	$q_deb = $values['deb'];
	$q_deb > 0 && var_dump($_POST);
	$q_url = $values['url'];
	$do = 'do' . $values['submit'];
	if ($do($values, $labels)) {
		$_POST = NULL;
		$form =& buildForm();
	}
}

function doDelete(&$values, &$labels) {
	foreach ($values['ids'] as $id => $flag) {
		assert($flag);
		$labels->delete(array('id' => $id));
		$labelmap->delete(array('labelId' => $id));
	}
	return True;
}

function doRename(&$values, &$labels) {
	$n = 0;
	foreach ($values['ids'] as $id => $flag) {
		assert($flag);
		if (empty($values[$id])) {
			echo 'New label name for must be nonempty<br>';
			$n++;
		} elseif (0) {
			echo 'New label name for must be unique<br>';
			$n++;
		}
	}
	if ($n > 0)
		return False;
	foreach ($values['ids'] as $id => $flag)
		$labels->update(array('id' => $id, 'label' => $values[$id]));
	return True;
}

function doAdd(&$values, &$labels) {
	$newlabel = $values['newlabel'];
	if (empty($newlabel)) {
		echo 'New label must be nonempty<br>';			
		return False;
	} elseif (0) {
		echo "$newlabel already exists";
		return False;
	} else
		$labels->create(array('label' => $newlabel));
	return True;
}

$form->display();
?>
