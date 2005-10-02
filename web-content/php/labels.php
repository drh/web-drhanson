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
	}
	
	function deleteItemId($id) {
		$q = $this->dbh->query("DELETE $this->labelmap FROM $this->labelmap WHERE itemId=$id");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		unset($this->labelsByItemId[$id]);
	}
	
	function deleteLabel($label) {
		$id = $this->getLabelId($label);
		if (!empty($id)) {
			$q = $this->dbh->query("DELETE $this->labels FROM $this->labels WHERE id=$id");
			if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
			$q = $this->dbh->query("DELETE $this->labelmap FROM $this->labelmap WHERE labelId=$id");
			if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
			$this->refresh();			
		}
	}
	
	function getLabel($id, $default=NULL) {
		if (array_key_exists($this->labelsById, $id))
			return $this->labelsById[$id];
		return $default;
	}
	
	function getLabelId($label) {
		foreach ($this->labelsById as $id => $lab)
			if ($label == $lab)
				return $id;
		return NULL;
	}
	
	function getItemIdsForLabel($label) {
	}
	
	function &getLabelsForItemId($id, $default=array()) {
		if (array_key_exists($id, $this->labelsByItemId))
			return $this->labelsByItemId[$id];
		return $default;
	}
	
	function refresh() {
		$q = $this->dbh->query("SELECT id, label FROM $this->labels");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$this->labelsById = array();
		while ($status = $q->fetchInto($row, DB_FETCHMODE_ORDERED)) {
			if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
			$this->labelsById[(int)$row[0]] = $row[1];
		}
		$q = $this->dbh->query("SELECT itemId, labelId FROM $this->labelmap");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$this->labelsByItemId = array();
		while ($status = $q->fetchInto($row, DB_FETCHMODE_ORDERED)) {
			if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
			$itemId = (int)$row[0];
			$this->labelsByItemId[$itemId][] = $this->labelsById[(int)$row[1]];
		}
	}
		
}

?>