<?php
require_once('util.php');
require_once('model.php');
require_once('label.php');
require_once('labelmap.php');

class Item extends Model {
	var $labelmap;
	var $labels;

	function Item($labelmap='labelmap', $labels='labels', $tablename='items') {
		require('dsn.php');
		parent::Model($dsn, $tablename);
		$this->labels = new Label($dsn);
		$this->labelmap = new LabelMap($dsn, $labels, $labelmap);
	}

}
?>
