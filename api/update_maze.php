<?php
/**
 * Validates and updates the maze
 */
require_once '../includes/classes/api_builder_includes/class.API.inc.php';
require_once '../includes/classes/api_builder_includes/class.Database.inc.php';

// setup and instantiate the api
require_once '../includes/api_columns.include.php';
require_once '../includes/api_setup.include.php';

// Load config vars
require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/config.include.php";

$referer = $_SERVER["HTTP_REFERER"];
$referer = preg_replace( "/([?#&][^?&=#]+)=([^&#]*)/", "", $referer);

// Only allow POSTS from play page
if ($referer != $HOSTNAME . "/make" && $referer != $HOSTNAME . "/make/index.php" && $referer != $HOSTNAME . "/make/") {
    header('HTTP/1.0 403 Forbidden');
    die();
}

header('Content-Type: application/json');


// Input Validation
if (!isset($_POST))  {
    die(json_encode(['status' => 'error', 'message' => 'Nothing provided']));
}

if (!isset($_POST['maze']))  {
    die(json_encode(['status' => 'error', 'message' => 'Nothing provided']));
}

// Escape strings
$post_array = Database::clean($_POST);

// Create object from POST
$mazeData = new stdClass();
foreach ($post_array as $key => $val) {
    $mazeData->$key = $val;
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/classes/class.Maze2D.php');

$maze2D = new Maze2D(json_decode($mazeData->maze), 800); //can pick random stage width (800) for this purpose

$maze2D->initLocations($mazeData);

// Validate maze before updating database
if (!$maze2D->validate()) {
    die(json_encode(['status' => 'error', 'message' => 'Error validating maze']));
}

if(!Database::execute_from_assoc($mazeData, Database::$table)){
    die(json_encode(['status' => 'error', 'message' => 'Database error']));
}

die(json_encode(['status' => 'success']));
?>