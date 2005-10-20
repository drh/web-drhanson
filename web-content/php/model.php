<?php
require_once('util.php');
require_once('DB.php');

class Model {
	var $db;
	var $tablename;
	var $dsn;
	var $fields;
	
	function Model($dsn, $tablename='items') {
		$this->tablename = $tablename;
		$this->dsn = $dsn;
		$this->db = DB::connect($dsn);
		if (DB::iserror($this->db)) {
			pre_var_dump($dsn);
			die(__FILE__ . '.' . __LINE__ . ': ' . $this->db->getMessage());
		}
		$this->db->setFetchMode(DB_FETCHMODE_ASSOC);
		$fields = array();
		$items =& $this->_getall("DESCRIBE $this->tablename");
		foreach ($items as $i => $row)
			$this->fields[] = $row['Field'];
	}

	function &create(&$params) {
		$vals = array();
		$sql =& $this->_join(',', $params, $vals, 'id');
		$this->_query("INSERT $this->tablename SET " . $sql, $vals);
		$items =& $this->_getall("SELECT * FROM $this->tablename WHERE id=LAST_INSERT_ID()");
		return $items[0];
	}

	function delete($params) {
		$vals = array();
		$sql =& $this->_join(' AND ', $params, $vals);
		$this->_query("DELETE $this->tablename FROM $this->tablename WHERE " . $sql, $vals);
	}
	
	function &find_all($sort_by='') {
		$sql = "SELECT * FROM $this->tablename";
		if (!empty($sort_by))
			$sql .= " ORDER BY $sort_by ASC";
		$items =& $this->_getall($sql);
		return $items;
	}

	function &find($conds=array(), $sort_by='') {
		$vals = array();
		$sql =& $this->_join(' AND ', $conds, $vals);
		if (strlen($sql) > 0)
			$sql = ' WHERE ' . $sql;
		if (!empty($sort_by))
			$sql .= " ORDER BY $sort_by ASC";
		$items = $this->_getall("SELECT * FROM $this->tablename" . $sql, $vals);
		return $items;
	}

	function &update(&$params) {
		$vals = array();
		$sql =& $this->_join(',', $params, $vals, 'id');
		$sql = "UPDATE $this->tablename SET " . $sql .'WHERE id=' . $params['id'];
		$this->_query($sql, $vals);
		$items =& $this->_getall("SELECT * FROM $this->tablename WHERE id=" . $params['id']);
		return $items[0];
	}

	function _getone($sql, $vals=array()) {
		$result = $this->db->getOne($sql, $vals);
		if (DB::iserror($result)) {
			pre_var_dump($sql);	pre_var_dump($vals);
			die(__FILE__ . '.' . __LINE__ . ': ' . $result->getMessage());
		}
		return $result;
	}

	function &_getall($sql, $vals=array()) {
		$result = $this->db->getAll($sql, $vals);
		if (DB::iserror($result)) {
			pre_var_dump($sql);	pre_var_dump($vals);
			die(__FILE__ . '.' . __LINE__ . ': ' . $result->getMessage());
		}
		return $result;
	}

	function &_join($glue, $params, &$vals, $exclude='') {
		$str = '';
	  foreach ($params as $k => $v)
			if ($k != $exclude && in_array($k, $this->fields)) {
				$str .= $glue . "$k=?";
				$vals[] = $v;
			}
		if (strlen($str) > 0)
			$str = substr($str, strlen($glue));
		return $str;
	}
	
	function _query($sql, $vals=array()) {
		$result = $this->db->query($sql, $vals);
		if (DB::iserror($result)) {
			pre_var_dump($sql);	pre_var_dump($vals);
			die(__FILE__ . '.' . __LINE__ . ': ' . $result->getMessage());
		}
		return $result;
	}

}
?>
