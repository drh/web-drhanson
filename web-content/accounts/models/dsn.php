<?php
if ($_ENV['PHP_ENV'] == 'production') {
	$dsn = array(
		'phptype'  => 'mysql',
		'username' => 'drh_w',
  	'password' => 'Dgig6hb7H3NGt1e',
		'hostspec' => 'db70c.pair.com',
  	'database' => 'drh_accounts'
	);
	return;
}

$dsn = array(
	'phptype'  => 'mysql',
	'username' => 'drh',
	'password' => '323-hpo',
	'hostspec' => 'localhost',
	'database' => 'accounts'
);					 
?>
