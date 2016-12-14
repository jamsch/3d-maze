<?php
    /**
     * Downloads a specified file from the findersfolder
     * Used on /play
     */
	if (!isset($_GET["file"]) || !isset($_SERVER["HTTP_REFERER"])) {
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

    $attachment_location = $_SERVER["DOCUMENT_ROOT"] . "/uploads/findersfolder/" . urldecode($_GET["file"]);

    //todo Check brute downloading
    if (!file_exists($attachment_location))
    {
        die("You took too long to download :^(. File no longer exists");
    }

    $findersfolder_uploads = file_get_contents("../includes/secret/findersfolder_uploads.json");

    if (!$uploads = json_decode($findersfolder_uploads, true)) {
        header('HTTP/1.0 403 Forbidden');
        die();
    }

    $original_filename = "";

    foreach ($uploads['files'] as $file) {
        if ($file['file'] == $_GET["file"]) {
            $original_filename = $file['name'];
            break;
        }
    }

    if (empty($original_filename))
    {
        header('HTTP/1.0 404 Not Found');
        die();
    }

    // Stream file to user
    header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
    header("Cache-Control: public"); // needed for internet explorer
    header("Content-Type: " . mime_content_type($attachment_location));
    header("Content-Transfer-Encoding: Binary");
    header("Content-Length: " . filesize($attachment_location));
    header("Content-disposition: attachment; filename=$original_filename");

    readfile($attachment_location);
?>