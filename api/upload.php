<?php
/**
 *  Handles file uploads from /play
 */
session_start();
// Load utility functions
require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/util.include.php";
// Load config vars
require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/config.include.php";
// Load file validation vars
require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/filevalidation.include.php";

$finders_folder_directory = $_SERVER['DOCUMENT_ROOT'] . "/uploads/findersfolder";

$upload_directory = $_SERVER['DOCUMENT_ROOT'] . "/uploads";

$archive_directory = $_SERVER['DOCUMENT_ROOT'] . "/file_archive";

$return_data = [];

header('Content-type: application/json');

// todo: Check brute uploading
// Make sure person is uploading only from the play page
$referer = preg_replace( "/([?#&][^?&=#]+)=([^&#]*)/", "", $_SERVER["HTTP_REFERER"]);

if ($referer != $HOSTNAME . "/play" &&  $referer != $HOSTNAME . "/play/index.php" && $referer != $HOSTNAME . "/play/") {
    header('HTTP/1.0 403 Forbidden');
    die();
}

if (!isset($_FILES)) die(json_encode(['status' => 'error', 'message'=>'No file uploaded']));
if (!isset($_FILES['end'])) die(json_encode(['status' => 'error', 'message'=>'No file uploaded']));
if (!isset($_POST['g-recaptcha-response'])) die(json_encode(['status' => 'error', 'message'=>'CAPTCHA not filled in']));

// User somehow doesn't have a token
if (!isset($_POST['token'], $_SESSION['token'], $_SESSION['has_uploaded'])) {
    header('HTTP/1.0 403 Forbidden');
    die();
}

// User's token is different
if ($_SESSION['token'] != $_POST['token']) {
    die(json_encode(['status' => 'error', 'message'=>'Unable to add file. Did you open the game in another window?']));
}

if ($_SESSION['has_uploaded']) {
    die(json_encode(['status' => 'error', 'message'=> "You can only upload one file :^("]));
}

$file = $_FILES["end"];

if ($file["size"] > $max_size || $file["error"] == 4) {
    die(json_encode(['status'=>'error', 'message'=>'pls stop uploading big files :^(']));
}

$file_base_name = pathinfo($file["name"], PATHINFO_FILENAME);
$extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

// Validate file type ($allowed_exts found in filevalidation.include.php)
if (!in_array($extension, $allowed_exts)) {
    die(json_encode(['status'=>'error', 'message'=>'Incorrect file type']));
}

// File has an error
if(!empty($file["error"])) {
    die(json_encode(['status'=>'error', 'message'=>'Unknown file error']));
}

// Validate CAPTCHA with Google
//https://www.google.com/recaptcha/api/siteverify
require_once '../includes/classes/recaptcha/autoload.php';

$secret_key = include '../includes/secret/recaptcha-secret-key.php';

$recaptcha = new \ReCaptcha\ReCaptcha($secret_key);
$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
if (!$resp->isSuccess()) {
    die(json_encode(['status' => 'captcha', 'message' => 'Looks like CAPTCHA malfunctioned :^(']));
}

// Validate comment
$comment = '';
if (isset($_POST['comment'])) {
    $comment_length = strlen($_POST['comment']);
    if ($comment_length > 255) {
        die(json_encode(['status' => 'error', 'message' => 'Your comment looks like spam']));
    }
    if ($comment_length > 0) {
        if (!$comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING)) {
            die(json_encode(['status' => 'error', 'message' => 'Your comment looks like spam']));
        }
    }
}

$findersfolder_uploads = file_get_contents("../includes/secret/findersfolder_uploads.json");

if (!$uploads = json_decode($findersfolder_uploads, true)) {
    die(json_encode(['status' => 'error', 'message' => 'Error found while scanning directory']));
}

$num_files = count($uploads['files']);

// Delete the last file
$last_file = $uploads['files'][$num_files - 1]['file'];

// Move last file to archive
rename("$finders_folder_directory/$last_file", "$archive_directory/$last_file");

// Save the uploaded file
$file_info = [
    'file' => getRandomString() . '.' . $extension,
    'name' => $file['name'],
    'comment' => $comment
];

// Add file to start of array
array_unshift($uploads['files'], $file_info);

// Remove last file from findersfolder array
array_pop($uploads['files']);

// Move uploaded file to directory
move_uploaded_file($file["tmp_name"], $finders_folder_directory . "/" . $file_info['file']);

// Re-Zip files in the folder
$zip_filename = getRandomString();
if (!$result = zip_folder($finders_folder_directory, $zip_filename, $upload_directory)) {
    rename("$archive_directory/$last_file", "$finders_folder_directory/$last_file");   // Move last file back
    die(json_encode(['status'=>'error', 'message'=>'There was a problem zipping up the files in the folder']));
}

$old_zip = $uploads['findersfolder'];

$uploads['findersfolder'] = $zip_filename . '.zip';

// Update array of findersfolder items
file_put_contents("../includes/secret/findersfolder_uploads.json", json_encode($uploads));

// Delete old zip file
unlink("$upload_directory/$old_zip");

// Keep temporary record that user has uploaded a file in their session
$_SESSION['has_uploaded'] = true;

// Notify user upload status
die(json_encode(['status'=>'success', 'message' => 'File successfully uploaded']));
?>