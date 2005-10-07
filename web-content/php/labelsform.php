<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<?php
require_once('util.php');
require_once('labels.php');
require_once('DB.php');
require_once('HTML/QuickForm.php');

$q_url = null;
$q_deb = 0;
extract($_GET, EXTR_PREFIX_ALL, 'q');

function buildForm(&$labels) {
	global $q_url, $q_deb;
	$form = new HTML_QuickForm('labels', 'post', '', '_self', NULL, True);
	foreach ($labels->getLabels() as $id => $lab) {
		$group[1] =& HTML_QuickForm::createElement('checkbox', "ids[$id]");
		$group[2] =& HTML_QuickForm::createElement('text', $id, '');
		$group[2]->setValue($lab);
		$form->addGroup($group, null);
	}
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

$form =& buildForm($labels);
if ($form->validate()) {
	$values = $form->getSubmitValues();
	$q_deb = $values['deb'];
	$q_deb > 0 && var_dump($_POST);
	$q_url = $values['url'];
	$do = 'do' . $values['submit'];
	if ($do($values, $labels)) {
		$_POST = NULL;
		$form =& buildForm($labels);
	}
}

function doDelete(&$values, &$labels) {
	foreach ($values['ids'] as $id => $flag)
		if ($flag)
			echo $labels->deleteLabelById($id);
	return True;
}

function doRename(&$values, &$labels) {
	$n = 0;
	foreach ($values['ids'] as $id => $flag)
		if ($flag && empty($values[$id])) {
			echo 'New label name for ' . $labels->getLabel($id) . ' must be nonempty<br>';
			$n++;
		} elseif ($flag && $labels->getLabelId($values[$id])) {
			echo 'New label name for ' . $labels->getLabel($id) . ' must be unique<br>';
			$n++;
		}
	if ($n > 0)
		return False;
	foreach ($values['ids'] as $id => $flag)
		if ($flag)
			$labels->renameLabelById($id, $values[$id]);
	return True;
}

function doAdd(&$values, &$labels) {
	$newlabel = $values['newlabel'];
	if (empty($newlabel)) {
		echo 'New label must be nonempty<br>';			
		return False;
	} elseif ($labels->getLabelId($newlabel)) {
		echo "$newlabel already exists";
		return False;
	}
	echo $labels->addLabel($newlabel);
	return True;
}
if (!empty($q_url))
	echo '<p>', html_link(urldecode($q_url), 'Back to main page'), '</p>';
$form->display();
if (!empty($q_url))
	echo '<p>', html_link(urldecode($q_url), 'Back to main page'), '</p>';
?>