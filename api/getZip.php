<?php
/**
 * Returns findersfolder details. Used by /scripts/three/classes/Maze3D.js for downloading files
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

$findersfolder_uploads = file_get_contents("../includes/secret/findersfolder_uploads.json");

if (!$findersfolder = json_decode($findersfolder_uploads, true)["findersfolder"]) {
    die(json_encode(['status' => 'error', 'message' => 'Error found while scanning directory']));
}

echo json_encode(['status' => 'success', 'folder' => $findersfolder]);

?>