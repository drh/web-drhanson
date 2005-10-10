<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<?php
require_once('DB.php');

/**
 * Manages database tables of (id,label) pairs and (itemId,labelId) mappings.
 */
class Labels {
	var $dbh;
	var $labelmap;       // table name for (itemId,labelId); default 'labelmap'
	var $labels;         // table name for (id,label); default 'labels'
	var $labelsById;     // id => label
	var $labelsByItemId; // itemId => array(id1 => 'label1', ..., idN => 'labelN')

	/**
	 * Constructs an instance with the given database connection handle and
	 * table names.
	 */
	function Labels($dbhandle, $labelmap = 'labelmap', $labels = 'labels') {
		$this->dbh = $dbhandle;
		$this->labelmap = $labelmap;
		$this->labels = $labels;
		$this->refresh();
	}
	
	/**
	 * Adds the label given by $label. Returns the id of the new label
	 * or NULL if $label already exists or $label is NULL.
	 */
	function addLabel($label) {
		if (empty($label) || $this->getLabelId($label))
			return NULL;
		$q = $this->dbh->query("INSERT $this->labels SET label=?", array($label));
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$this->_refreshLabels();
		return $this->getLabelId($label);
	}
	
	/**
	 * Adds the label identified by $labelId to $itemId; that is, add
	 * the ($itemId,$labelId) entry. Returns True if $labelId exists and this entry
	 * does not exist and is added, and False otherwise.
	 */
	function addLabelById($itemId, $labelId) {
		if (!$this->getLabel($labelId) || array_key_exists($labelId, $this->getLabelsForItemId($itemId)))
			return False;
		$q = $this->dbh->query("INSERT $this->labelmap SET itemId=$itemId,labelId=$labelId");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$this->_refreshLabelMap();
		return True;
	}
	
	/**
	 * Deletes the (itemId,labelId) entry for itemId==$id.
	 */
	function deleteItemId($id) {
		$q = $this->dbh->query("DELETE $this->labelmap FROM $this->labelmap WHERE itemId=$id");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		unset($this->labelsByItemId[$id]);
	}
	
	/**
	 * Deletes the label given $label and the (itemId,labelId) mappings that refer to $label.
	 * Returns True if $label existed and was deleted, and False otherwise.
	 */
	function deleteLabel($label) {
		$id = $this->getLabelId($label);
		return $this->deleteLabelById($id);
	}

	/**
	 * Deletes the label identified by $id and the (itemId,labelId) mappings where labelId==$id.
	 * Returns True if $label existed and was deleted, and False otherwise.
	 */
	function deleteLabelById($id) {
		if (!array_key_exists($id, $this->labelsById))
			return False;
		$q = $this->dbh->query("DELETE $this->labels FROM $this->labels WHERE id=$id");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$q = $this->dbh->query("DELETE $this->labelmap FROM $this->labelmap WHERE labelId=$id");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$this->refresh();			
		return True;
	}
	
	/**
	 * Returns the label identified by $id, or $default if $id does not exist.
	 */	
	function getLabel($id, $default=NULL) {
		if (array_key_exists($id, $this->labelsById))
			return $this->labelsById[$id];
		return $default;
	}
	
	/**
	 * Returns the (id,label) mapping as a reference to array(id1 => 'label1', ..., idN => 'labelN').
	 */
	function &getLabels() {
		return $this->labelsById;
	}
	
	/**
	 * Returns the id for the label given by $label, or False if $label does not exist.
	 */
	function getLabelId($label) {
		return array_search($label, $this->labelsById);
	}
	
	/**
	 * Returns a reference to an array of the itemIds associated with $label, or $default
	 * if $label does not exist.
	 */
	function &getItemIdsForLabel($label, $default=array()) {
		$id = $this->getLabelId($label);
		if (empty($id))
			return $default;
		$q = $this->dbh->query("SELECT itemId FROM $this->labelmap WHERE labelId=$id");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$result = array();
		while ($status = $q->fetchInto($row, DB_FETCHMODE_OBJECT)) {
			if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
			$result[] = (int)$row->itemID;
		}
		return $result;
	}
	
	/**
	 * Returns a reference to an array(id1 => 'label1', ..., idN => 'labelN') of the labels
	 * associated with the $itemId, or $default if $itemId has no labels.
	 */
	function &getLabelsForItemId($itemId, $default=array()) {
		if (array_key_exists($itemId, $this->labelsByItemId))
			return $this->labelsByItemId[$itemId];
		return $default;
	}
	
	function _refreshLabels() {
		$q = $this->dbh->query("SELECT id, label FROM $this->labels");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$this->labelsById = array();
		while ($status = $q->fetchInto($row, DB_FETCHMODE_OBJECT)) {
			if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
			$this->labelsById[(int)$row->id] = $row->label;
		}
		asort($this->labelsById);
	}
	
	function _refreshLabelMap() {
		$q = $this->dbh->query("SELECT itemId, labelId FROM $this->labelmap");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$this->labelsByItemId = array();
		while ($status = $q->fetchInto($row, DB_FETCHMODE_OBJECT)) {
			if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
			$this->labelsByItemId[$row->itemId][$row->labelId] = $this->labelsById[$row->labelId];
		}
	}
	
	/**
	 * Refreshes the label data structures from the database, if, e.g., the database is
	 * modified outside this class.
	 */
	function refresh() {
		$this->_refreshLabels();
		$this->_refreshLabelMap();
	}
	
	/**
	 * Removes the label identified by $labelId from $itemId; that is, remove
	 * the ($itemId,$labelId) entry. Returns True if this entry exists and is removed,
	 * and False otherwise.
	 */
	function removeLabelById($itemId, $labelId) {
		if (!array_key_exists($itemId,  $this->labelsByItemId)
		||  !array_key_exists($labelId, $this->labelsByItemId[$itemId]))
			return False;
		$q = $this->dbh->query("DELETE $this->labelmap FROM $this->labelmap 
			WHERE itemId=$itemId AND labelId=$labelId");
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		unset($this->labelsByItemId[$itemId][$labelId]);
		return True;
	}
	
	/**
	 * Renames label $label to $newlabel. Returns True if $newlabel is notnull and
	 * if $label exists and is renamed successfully, and False otherwise.
	 */
	function renameLabel($label, $newlabel) {
		$id = $this->getLabelId($label);
		return $this->renameLabelById($id, $newlabel);
	}

	/**
	 * Renames the label identified by $id to $newlabel. Returns True if $newlabel is notnull and
	 * if $label exists and is renamed successfully, and False otherwise.
	 */
	function renameLabelById($id, $newlabel) {
		if (empty($newlabel) || $this->getLabelId($newlabel))
			return False;
		$q = $this->dbh->query("UPDATE $this->labels SET label=? WHERE id=?", array($newlabel, $id));
		if (DB::iserror($q)) die(__FILE__ . '.' . __LINE__ . ': ' . $q->getMessage());
		$this->labelsById[$id] = $newlabel;
		$this->_refreshLabelMap();
		return True;
	}
	
	/**
	 * Changes the set of labels associated with $itemID to those $labelIds,
	 * which an array(id1, ..., idN).
	 */
	function updateLabelsForItemId($itemId, &$labelIds) {
		$cur = array_keys($this->getLabelsForItemId($itemId));
		if (empty($labelIds))
			$labelIds = array();
		foreach (array_diff($cur, $labelIds) as $id)
			$this->removeLabelById($itemId, $id);
		foreach (array_diff($labelIds, $cur) as $id)
			$this->addLabelById($itemId, $id);		
	}
		
}
?>
