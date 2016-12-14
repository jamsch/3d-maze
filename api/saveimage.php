<?php
    /**
     * Used by /draw/index.php page to save images
     * Called in WallSegment saveImage function
     * POST params: filename (texture filename), base64 (containing image)
     */
	require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.include.php';

	$images_folder = $_SERVER['DOCUMENT_ROOT'] . "/images/maze/textures";
	$small_images_folder = $_SERVER['DOCUMENT_ROOT'] . "/images/maze/textures_small";
	$small_image_size = 256;

    header('Content-Type: application/json');

	if (!isset($_SERVER["HTTP_REFERER"]) || !isset($_POST["filename"]) || !isset($_POST["base64"])) {
        die(json_encode(['status' => 'error', 'message' => "Unknown error"]));
    }

    if (!$filename = filter_input(INPUT_POST, 'filename', FILTER_SANITIZE_STRING)) {
        die(json_encode(['status' => 'error', 'message' => "Unknown error"]));
    }

    $base64String = urldecode($_POST["base64"]);
    $base64String = str_replace('data:image/png;base64,', '', $base64String);

    if (!$canvas_image = base64_decode($base64String)) {
        die(json_encode(['status' => 'error', 'message' => "Failed to decode file"]));
    }

    // Write base64 string to file
    if (!$bytes_written = file_put_contents($images_folder . "/" . $filename, $canvas_image)) {
        die(json_encode(['status' => 'error', 'message' => "Error writing file"]));
    }

    //require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/classes/class.SimpleImage.php';
    // Resize texture to 256x256 texture and place in the small images folder
   // $image = new SimpleImage();

    // compress image and place in temp folder
   // $image->compress_png($images_folder . "/" . $filename, 100);

   // $image->load($canvas_image, true);
   // $image->resize($small_image_size, $small_image_size);
   // $image->save($small_images_folder . "/" . $filename, IMAGETYPE_PNG, 9);
    // Compress small texture
   // $image->compress_png($small_images_folder . "/" . $filename, 100);

    die(json_encode(['status' => 'success']));
?>