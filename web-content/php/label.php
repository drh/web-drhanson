<?php
require_once('model.php');

/**
 * Manages database table of (id,label) pairs
 */
class Label extends Model {

	function Label($dsn, $tablename='labels') {
		parent::Model($dsn, $tablename);
	}

}
?>
