<?php

// Kippo Enhancer v1 by kill0rz - visit http://kill0rz.com/

// This tool reads your kippo database and kippo logs available on your server.
// It will analyse it and put every possible user/password combination into your database.

// !ATTENTION!
// Depending on your incoming kippo-traffic, your databse will become very big very quickly.
// You should not run this script too often. Be careful in use.
// A backup of all logs and the database is stored everytime the script is running.

// If you have a bigger databse or a lot of logs, give this script enough RAM to run correctly, I suggest 1G.
// Insert your directory to kippo with an / at the end below:

$kippodir = "/home/kippo/kippo/";
ini_set('memory_limit', '1G');

//

$dbdir = $kippodir . "data/userdb.txt";
$logdir = $kippodir . "log/kippo.log";
$olddb = file($dbdir);
$logs = file($logdir);
copy($logdir, $logdir . "_" . time());
file_put_contents($logdir . "_" . time(), $logs);
file_put_contents($logdir, "");

$i = 1;
while (file_exists($logdir . "." . $i)) {
	$logs = array_merge($logs, file($logdir . "." . $i));
	copy($logdir . "." . $i, $logdir . "." . $i . "_" . time());
	unlink($logdir . "." . $i);
	$i++;
}

$old_data = count($olddb);
$old_users = array();
$old_passes = array();

foreach ($olddb as $olddb) {
	$tmp = explode(":", $olddb);
	$old_users[] = trim($tmp['0']);
	$old_passes[] = trim($tmp['2']);
}
$olddb = '';

natsort($old_passes);
natsort($old_users);

$old_passes = array_unique($old_passes);
$old_users = array_unique($old_users);

$old_passes_count = count($old_passes);
$old_users_count = count($old_users);

$new_passes = array();
$new_users = array();
foreach ($logs as $l) {
	if ($l != str_replace("] login attempt", "", $l) && $l != str_replace("] failed", "", $l)) {
		$new_passes[] = substr(str_replace("] failed", "", strstr($l, "/")), 1);
		$new_users[] = str_replace(array("] login attempt [", " ", "/" . trim(substr(str_replace("] failed", "", strstr($l, "/")), 1)) . "]failed"), "", substr($l, strpos($l, "] login attempt [")));
	}
}

natsort($new_users);
natsort($new_passes);
$new_users = array_unique($new_users);
$new_passes = array_unique($new_passes);

$users = array_merge($new_users, $old_users);
$passes = array_merge($new_passes, $old_passes);

natsort($users);
natsort($passes);
$users = array_map('trim', $users);
$passes = array_map('trim', $passes);
$users = array_filter($users);
$passes = array_filter($passes);
$users = array_unique($users);
$passes = array_unique($passes);

$new_passes_count = count($passes);
$new_users_count = count($users);

$new_data_to_write = array();
foreach ($passes as $pass) {
	foreach ($users as $user) {
		if (trim($user) != '' && trim($pass) != '') {
			$new_data_to_write[] = trim($user) . ":0:" . trim($pass) . "\n";
		}

	}
}
natsort($new_data_to_write);
$new_data_to_write = array_unique($new_data_to_write);
$new_data = count($new_data_to_write);

?>

<html>

<head>
	<title>Kippo Enhancer v1</title>
</head>

<body>


<?php
copy($dbdir, $dbdir . "_" . time());

file_put_contents($dbdir, "");

foreach ($new_data_to_write as $line) {
	file_put_contents($dbdir, $line, FILE_APPEND);
}

?>
<table border="1">
	<tr>
		<th></th>
		<th>Vorher</th>
		<th>Nachher</th>
	</tr>
	<tr>
		<td>Nutzer</td>
		<td>
			<?php echo $old_users_count; ?>
		</td>
		<td>
			<?php echo $new_users_count; ?>
		</td>
	</tr>
	<tr>
		<td>Passwörter</td>
		<td>
			<?php echo $old_passes_count; ?>
		</td>
		<td>
			<?php echo $new_passes_count; ?>
		</td>
	</tr>
	<tr>
		<td>Kombinationen gesamt</td>
		<td>
			<?php echo $old_data; ?>
		</td>
		<td>
			<?php echo $new_data; ?>
		</td>
	</tr>
</table>


<?php

$ausgabearray = array();

foreach ($new_users as $aa) {
	if (trim($aa) != '' && !in_array(trim($aa), $old_users)) {
		$ausgabearray[] = $aa . "<br /> \n";
	}

}

echo "<h2>Folgende User sind neu (" . count($ausgabearray) . " Einträge):</h2>";

foreach ($ausgabearray as $ausgabearray) {
	echo $ausgabearray;
}
