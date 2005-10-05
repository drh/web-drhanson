<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<?php
require_once('DB.php');

class Labels {
	var $dbh;
	var $labelmap;
	var $labels;
	var $labelsById;
	var $labelsByItemId;

	function Labels($dbhandle, $labelmap = 'labelmap', $labels = 'labels') {
		$this->dbh = $dbhandle;
		$this->labelmap = $labelmap;
		$this->labels = $labels;
		$this->refresh();
	}
	
	function addLabel($label) {
		if ($this->getLabelId($label))
			return NULL;
		$q = $this->dbh->query("INSERT $this->labels SET label=?", array($label));
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$this->_refreshLabels();
		return $this->getLabelId($label);		
	}
	
	function deleteItemId($id) {
		$q = $this->dbh->query("DELETE $this->labelmap FROM $this->labelmap WHERE itemId=$id");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		unset($this->labelsByItemId[$id]);
	}
	
	function deleteLabel($label) {
		$id = $this->getLabelId($label);
		return $this->deleteLabelById($id);
	}

	function deleteLabelById($id) {
		if (array_key_exists($id, $this->labelsById)) {
			$q = $this->dbh->query("DELETE $this->labels FROM $this->labels WHERE id=$id");
			if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
			$q = $this->dbh->query("DELETE $this->labelmap FROM $this->labelmap WHERE labelId=$id");
			if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
			$this->refresh();			
			return True;
		}
		return False;
	}
	
	function getLabel($id, $default=NULL) {
		if (array_key_exists($id, $this->labelsById))
			return $this->labelsById[$id];
		return $default;
	}
	
	function &getLabels() {
		return $this->labelsById;
	}
	
	function getLabelId($label) {
		return array_search($label, $this->labelsById);
	}
	
	function &getItemIdsForLabel($label, $default=array()) {
		$id = $this->getLabelId($label);
		if (empty($id))
			return $default;
		$q = $this->dbh->query("SELECT itemId
														FROM $this->labelmap,$this->labels
														WHERE label=?", array($label));
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$result = array();
		while ($status = $q->fetchInto($row, DB_FETCHMODE_ORDERED)) {
			if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
			$result[] = (int)$row[0];
		}
		return $result;
	}
	
	function &getLabelsForItemId($id, $default=array()) {
		if (array_key_exists($id, $this->labelsByItemId))
			return $this->labelsByItemId[$id];
		return $default;
	}
	
	function _refreshLabels() {
		$q = $this->dbh->query("SELECT id, label FROM $this->labels");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$this->labelsById = array();
		while ($status = $q->fetchInto($row, DB_FETCHMODE_ORDERED)) {
			if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
			$this->labelsById[(int)$row[0]] = $row[1];
		}
	}
	
	function _refreshLabelMap() {
		$q = $this->dbh->query("SELECT itemId, labelId FROM $this->labelmap");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$this->labelsByItemId = array();
		while ($status = $q->fetchInto($row, DB_FETCHMODE_ORDERED)) {
			if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
			$itemId = (int)$row[0];
			$this->labelsByItemId[$itemId][] = $this->labelsById[(int)$row[1]];
		}
	}
	
	function refresh() {
		$this->_refreshLabels();
		$this->_refreshLabelMap();
	}
	
	function renameLabel($label, $newlabel) {
		$id = $this->getLabelId($label);
		return $this->renameLabelById($id, $newlabel);
	}

	function renameLabelById($id, $newlabel) {
		if (empty($newlabel) || $this->getLabelId($newlabel))
			return False;
		$q = $this->dbh->query("UPDATE $this->labels SET label=? WHERE id=?", array($newlabel, $id));
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$this->labelsById[$id] = $newlabel;
		$this->_refreshLabelMap();
		return True;
	}
		
}
?>
