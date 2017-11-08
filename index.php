<?php
include 'db.php';
require('../events/MCAPI.class.php');
require 'vendor/autoload.php';
require_once('../events/includes/PHPMailer-master/PHPMailerAutoload.php');
date_default_timezone_set('America/New_York');

/* Register all the classes include */
spl_autoload_register(function ($class_name) {
	set_include_path('src/controllers/');
	$arr = preg_split('/(?=[A-Z])/', $class_name);
	$splitName = strtolower($arr[1]);
    require_once $splitName . '.php';
});

$app = new Slim\App();

/* Register all the routes include */
$files = glob($dir . 'src/routes/*.php');

foreach ($files as $file) {
    require_once($file);   
}

/* Test your app */

$app->get('/', function ($request, $response, $args) {
	$response->write("Welcome to UWUA");
	return $response;});

$app->get('/api-test', '\LoginController:test');

$app->run();