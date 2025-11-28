<?php

$db_host = 'localhost';
$db_name = 'cs2team62_game_store';
$username = 'cs2team62';
$password = 'GY0GxDiTfefavKmkkCh894dlb';

try {
	$db = new PDO("mysql:dbname=$db_name;host=$db_host", $username, $password); 
} catch(PDOException $ex) {
	echo("Failed to connect to the database.<br>");
	echo($ex->getMessage());
	exit;
}
?>