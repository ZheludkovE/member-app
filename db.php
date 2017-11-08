<?php
function getDB() { 
	$dbhost="localhost";
	$dbuser="admin";
	$dbpass="admin";
	$dbname="checkin_app";
	$dbConnection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbConnection;
	date_default_timezone_set('America/New_York');
}
?>