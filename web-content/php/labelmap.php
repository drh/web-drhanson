<?php
require_once('map.php');

/**
 * Manages database table of (item_id,label_id) pairs.
 */
class LabelMap extends Map {
	var $labels;

	function LabelMap($dsn, $labels='labels', $tablename='labelmap') {
		parent::Map($dsn, $tablename);
		$this->labels = $labels;
	}

	# returns an array of { 'item_id' => j, 'label_id' = k, 'label' => '...' }
	function &find_all() {
		$items =& $this->_getall(
			"SELECT item_id,label_id,label FROM $this->tablename,$this->labels " .
			"WHERE label_id=id ORDER BY item_id");
		return $items;
	}

	# returns an array of { 'label_id' = k, 'label' => '...', 'item_count' => n }
	function &find_all_counts() {
		$items =& $this->_getall(
			'SELECT id,label,COUNT(label_id) AS item_count ' .
			"FROM $this->labels LEFT JOIN $this->tablename ON id=label_id " .
			'GROUP BY label,label_id ORDER BY label');
		return $items;
	}

	# returns an array of { 'item_id' => j, 'label_id' = k, 'label' => '...' }
	function &find($conds=array(), $sort_by='item_id') {
		$vals = array();
		$sql =& $this->_join(' AND ', $conds, $vals);
		if (strlen($sql) > 0)
			$sql = ' WHERE label_id=id AND ' . $sql;
		else
			$sql = ' WHERE label_id=id';
		if (!empty($sort_by))
			$sql .= " ORDER BY $sort_by ASC";
		$items =& $this->_getall(
			"SELECT item_id,label_id,label FROM $this->tablename,$this->labels " . $sql, $vals);
		return $items;
	}

}
?>
