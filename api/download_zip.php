<?php
/**
 * Downloads a specified file from the findersfolder
 * Used on /play
 */

session_start();

if (!isset($_SERVER["HTTP_REFERER"], $_SESSION['has_downloaded_folder'])) {
    header('HTTP/1.0 403 Forbidden');
    die();
}

require_once '../includes/config.include.php';

$referer = $_SERVER["HTTP_REFERER"];
$referer = preg_replace( "/([?#&][^?&=#]+)=([^&#]*)/", "", $referer);

if ($referer != $HOSTNAME . "/play" &&  $referer != $HOSTNAME . "/play/index.php" && $referer != $HOSTNAME . "/play/") {
    header('HTTP/1.0 403 Forbidden');
    die();
}

if ($_SESSION['has_downloaded_folder']) {
    die("You've already downloaded the findersfolder this game.");
}

$upload_dir = $_SERVER["DOCUMENT_ROOT"] . "/uploads";
$findersfolder_uploads = file_get_contents("../includes/secret/findersfolder_uploads.json");

// todo Check brute downloading
if (!$uploads = json_decode($findersfolder_uploads, true)) {
    header('HTTP/1.0 403 Forbidden');
    die();
}

$attachment_location = $upload_dir . '/' . $uploads['findersfolder'];

header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
header("Cache-Control: public"); // needed for internet explorer
header("Content-Type: " . mime_content_type($attachment_location));
header("Content-Transfer-Encoding: Binary");
header("Content-Length: " . filesize($attachment_location));
header("Content-disposition: attachment; filename=findersfolder.zip");

readfile($attachment_location);
$_SESSION['has_downloaded_folder'] = true;
?>