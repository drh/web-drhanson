<!--
Generate one row of the list of items.

$item holds a record from items;
$labels holds the labels for this item;
$query holds the query parameters;
$row is row number.
-->
<?
extract($item, EXTR_PREFIX_ALL, '');
$_self = 'index.php';
$_names = explode(' ', $_owner);
if (!empty($_url) &&	!array_key_exists('scheme', parse_url($_url)))
	$_url = "http://$_url";
if ($_GET['action'] == 'showpw' && $_GET['id'] == $_id)
	$_password = link_to($_self, $_password, $query);
else
	$_password = link_to($_self, 'show',
											 array_merge($query, array('id' => $_id, 'action' => 'showpw')));
?>
<tr<?= $row%2 == 0 ? '' : ' class="shaded"' ?>>
	 <td><?= link_to($_url, $_name) ?></td>
	 <td><?= $_login ?></td>
	 <td><?= $_password ?></td>
	 <td><?= link_to($_self, $_names[0],
						array_merge($query, array('owner' => $_owner))) ?></td>
	 <td><?= $_number ?></td>
	 <td class='actions'><?=
		link_to('controllers/edititem.php', 'Edit',
						array_merge($query, array('id' => $_id))) . ' ' .
		link_to('controllers/deleteitem.php','Del',
						array_merge($query, array('id' => $_id))) ?></td>
	 <td class='labels'><?
		foreach ($labels as $_ => $label)
			if ($label != $query['label'])
				echo link_to($_self, $label,
					array('owner' => $query['owner'], 'label' => $label)) . ' ' ?></td>
</tr>
