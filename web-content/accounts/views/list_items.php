<!--
Generate the list of items as a table.
$query holds the query parameters.
-->
<?= link_to('controllers/edititem.php', 'Add Item', $query) ?>
<table border=0>
<tr>
	 <th>Name</th>
	 <th>Login</th>
	 <th>Password</th>
	 <th>Owner</th>
	 <th>Number</th>
	 <th></th>
	 <th align='right'><?=
		 link_to('controllers/editlabels.php', 'Edit Labels', $query) ?>
   </th>
</tr>
<tr class='shaded'>
	 <td><?= link_to('index.php', 'All') ?></td>
	 <td></td>
	 <td></td>
	 <td><?= link_to('index.php', 'All', array('owner' => '', 'label' => $query['query'])) ?></td>
	 <td></td>
	 <td></td>
	 <td align='right'><?=
		link_to('index.php', 'All', array('owner' => $query['owner'], 'label' => '')) ?></td>
</tr>
<?
$labelmap = $model->labelmap;
foreach ($items as $row => $item) {
	$labels = array();
	foreach ($labelmap->find(array('item_id' => $item['id'])) as $_ => $info)
		$labels[$info['label_id']] = $info['label'];
	if (empty($query['label']) || array_search($query['label'], $labels)) {
		require('views/display_item.php');
		$row++;
	}
}
?>
</table>
