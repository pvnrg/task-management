<?php
error_reporting(0);
require "config.php";
require 'libraries/jwt/src/JWT.php';
require "src/controller/ApiController.php";
require "src/modal/Main_model.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$action = $_REQUEST["action"];
try {
    $dbConnection = new \mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);

    // pass the request method and user ID to the Controller and process the HTTP request:
    $controller = new api($dbConnection, $action);
    $controller->processRequest();       
} catch (\Exception $e) {
    echo $e->getMessage();
}

