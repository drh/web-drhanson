<?php
require_once('model.php');

/**
 * Manages database table of (id,id) pairs relating two other tables
 */
class Map extends Model {

	function Map($dsn, $tablename='map') {
		parent::Model($dsn, $tablename);
	}

	function &create(&$params) {
		$vals = array();
		$sql =& $this->_join(',', $params, $vals);
		$this->_query("INSERT $this->tablename SET " . $sql, $vals);
		$vals = array();
		$sql = "SELECT * FROM $this->tablename WHERE " . $this->_join(' AND ', $params, $vals);
		$items =& $this->_getall($sql, $vals);
		return $item[0];
	}

}
?>
