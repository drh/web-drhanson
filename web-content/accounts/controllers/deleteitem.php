<?php
# delete an item
require_once('util.php');
require_once('../models/item.php');

extract($_GET, EXTR_PREFIX_ALL, '');
if ($_id > 0) {
	$items = new Item();
	$items->delete(array('id' => $_id));
	$items->labelmap->delete(array('item_id' => $_id));
}
redirect_to('../index.php', array('owner' => $_owner, 'label' => $_label))
?>
