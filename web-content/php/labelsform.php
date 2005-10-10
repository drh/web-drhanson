<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<?php
require_once('util.php');
require_once('labels.php');
require_once('DB.php');
require_once('HTML/QuickForm.php');

$q_url = null;
$q_deb = 0;
extract($_GET, EXTR_PREFIX_ALL, 'q');

function &buildForm(&$labels) {
	global $q_url, $q_deb, $counts;
	$form = new HTML_QuickForm('labels', 'post', '', '_self', NULL, True);
	$q = $labels->dbh->query("SELECT id,label,COUNT(labelId) AS count
		FROM $labels->labels LEFT JOIN $labels->labelmap ON id=labelId
		GROUP BY label,labelId
		ORDER BY label");
	if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
	while ($status = $q->fetchInto($row, DB_FETCHMODE_OBJECT)) {
		if (DB::iserror($status)) die(__FILE__ . '.' . __LINE__ . ': ' . $status->getMessage());
		$group[1] =& HTML_QuickForm::createElement('checkbox', "ids[$row->id]");
		$group[2] =& HTML_QuickForm::createElement('text', $row->id, '');
		$group[2]->setValue($row->label);
		$counts[$row->id] = $row->count;
		$group[3] =& HTML_QuickForm::createElement('static', '', '',
			'<small>(' . $row->count . ' items)</small>');
		$group[4] =& HTML_QuickForm::createElement('hidden', "count[$row->id]", $row->count);
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
	global $counts;
	if (function_exists('delete_condition')) {
		$n = 0;
		foreach ($values['ids'] as $id => $flag) {
			assert($flag);
			if (!delete_condition($id, $counts[$id]))
				$n++;
		}
		if ($n > 0)
			return False;
	}
	foreach ($values['ids'] as $id => $flag) {
		assert($flag);
		$labels->deleteLabelById($id);
	}
	return True;
}

function doRename(&$values, &$labels) {
	$n = 0;
	foreach ($values['ids'] as $id => $flag) {
		assert($flag);
		if (empty($values[$id])) {
			echo 'New label name for ' . $labels->getLabel($id) . ' must be nonempty<br>';
			$n++;
		} elseif ($labels->getLabelId($values[$id])) {
			echo 'New label name for ' . $labels->getLabel($id) . ' must be unique<br>';
			$n++;
		}
	}
	if ($n > 0)
		return False;
	foreach ($values['ids'] as $id => $flag)
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
	} else
		$labels->addLabel($newlabel);
	return True;
}
if (!empty($q_url))
	echo '<p>', html_link(urldecode($q_url), 'Back to main page'), '</p>';
$form->display();
if (!empty($q_url))
	echo '<p>', html_link(urldecode($q_url), 'Back to main page'), '</p>';
?>