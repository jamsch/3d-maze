<?php
/**
 * Returns file details. Used by /scripts/three/classes/Maze3D.js for downloading files
 * GET params: fileNumber
 * RETURNS: json array keys: status, message, obj
 */

// Load config vars
require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/config.include.php";

$referer = $_SERVER["HTTP_REFERER"];
$referer = preg_replace( "/([?#&][^?&=#]+)=([^&#]*)/", "", $referer);

// Only allow POSTS from play page
if ($referer != $HOSTNAME . "/play" &&  $referer != $HOSTNAME . "/play/index.php" && $referer != $HOSTNAME . "/play/") {
    header('HTTP/1.0 403 Forbidden');
    die();
}

header('Content-Type: application/json');

// Input Validation
if (!isset($_POST))  {
    die(json_encode(['status' => 'error', 'message' => 'No GET query provided']));
}

if (!isset($_POST['fileNumber'])) {
    die(json_encode(['status' => 'error', 'message' => 'File number not provided']));
}

if (!$fileNumber = filter_input(INPUT_POST, 'fileNumber', FILTER_SANITIZE_NUMBER_INT)) {
    if ($_POST['fileNumber'] == '0')  {
        $fileNumber = 0;
    } else {
        die(json_encode(['status' => 'error', 'message' => "Input parameter not a number - " ]));
    }
}

if ($fileNumber < 0) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid file number']));
}

$findersfolder_uploads = file_get_contents("../includes/secret/findersfolder_uploads.json");

if (!$files = json_decode($findersfolder_uploads, true)["files"]) {
    die(json_encode(['status' => 'error', 'message' => 'Error found while scanning directory']));
}

if ($fileNumber >= count($files)) {
    die(json_encode(['status' => 'error','message' => 'Invalid file number']));
}

echo json_encode(['status' => 'success', 'fileobject' => $files[$fileNumber]]);

?>