<?php
require 'DBQuery.php';
require '../db.php';
      
$db = new DBQuery(getDB());
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET'://query
        switch ($_GET['processcode']) {
        case "ZGLqmeKpUJQS":
            $db->automaticUpdate(); 
            break;
        default:
            break;
        }
        break;
    default:
        break;
}

