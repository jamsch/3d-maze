<?php
	require_once "../includes/filevalidation.include.php";
    // Generate one-time use form token
    session_start();
    $_SESSION['token'] = md5(microtime().rand());
    $_SESSION['has_uploaded'] = false;
    $_SESSION['has_downloaded_folder'] = false;
?>

<!DOCTYPE html>
<html>
	<head>
		<?php
            require_once "../includes/config.include.php";
            require_once "../includes/head.include.php";
	     ?>
	    <link rel="stylesheet" type="text/css" href="../styles/styles.css">
		<link rel="stylesheet" type="text/css" href="../styles/maze3d.css">
		<script src="../scripts/three/three.js"></script>
		<script src="../scripts/jquery-3.1.1.min.js"></script>
        <script src='https://www.google.com/recaptcha/api.js'></script>
		<script type="text/javascript" src="../scripts/helpers.js"></script>
		<script src="../scripts/three/classes/Maze3D.js"></script>
		<script src="../scripts/three/classes/Block3D.js"></script>
		<script src="../scripts/three/classes/Location3D.js"></script>
		<script src="../scripts/three/Detector.js"></script>
		<script src="../scripts/three/THREEx.KeyboardState.js"></script>
		<script src="../scripts/three/THREEx.WindowResize.js"></script>
		<script src="../scripts/three/MTLLoader.js"></script>
		<script src="../scripts/three/OBJMTLLoader.js"></script>
		<script src="../scripts/three/Stats.js"></script>
		<script src="../scripts/three/classes/CharacterController.js"></script>
		<script type="text/javascript">
			var fileInputVals = [];
			var allowedExtensions = <?= $allowed_exts_JSON ?> ; //don't forget semi
			var maxFileSize = <?= $max_size ?> ; //don't forget semi
			var errors = [];
			var fileUploadSelector = '#end-file';
			var fileUploadSuccess = false;
			var responsePending = false;
		</script>
		<script type="text/javascript" src="../scripts/fileupload.js"></script>
		<script>
			var hostname = '<?= $HOSTNAME ?>';
            var hasUploaded = false;
            var zip_file;

			$(document).ready(function() {
				if (!Detector.webgl) {
					var webGLError = '<p style="color: #fff">Oops, it looks like your browser doesn\'t support WebGL.<br>Trying using Google Chrome.</p>';
					$('#blocker').html(webGLError);
				}

				// Register "No thanks" button click event
				$('#no-submit').click(function() {
                    $('#end-container').hide();
				});

				// Register file upload button click event
				$('#submit').click(function() {
				    // Check if already uploaded
                    if (hasUploaded) {
                        notificationElement.attr('class', 'error notification');
                        $(notificationText).html("You've already uploaded a file in this session.");
                        return;
                    }

				    // Verify captcha
                    var captcha_response = grecaptcha.getResponse();
                    if (captcha_response.length == 0) {
                        notificationElement.attr('class', 'error notification');
                        notificationText.html("Please fill in the CAPTCHA");
                        return;
                    }

                    // Verify files
	                if (!onFilesSubmit()) return;

                    var data = new FormData();
                    if (typeof(document.querySelector("#end-file").files[0]) == 'undefined') {
                        notificationElement.attr('class', 'error notification');
                        notificationText.html("You didn't upload any file, silly goyim!");
                        return;
                    }

                    // Append POST keys
                    data.append('end',document.querySelector("#end-file").files[0]);
                    data.append('comment', document.querySelector(".file-upload-input-container>textarea").value);
                    data.append('token', document.querySelector("#form-token").value);
                    data.append('g-recaptcha-response', captcha_response);

                    if (!responsePending) {
                        notificationElement.attr('class', 'warning notification');
                        notificationText.html("Uploading...");

                        $.ajax({
                            url:'/api/upload.php',
                            type:'POST',
                            processData: false,
                            contentType: false,
                            data: data,
                            success: function(response) {
                                responsePending = false;
                                console.log(response);
                                switch(response['status']) {
                                    case "success":
                                        notificationText.html(response['message']);
                                        notificationElement.attr('class', 'success notification');
                                        hasUploaded = true;
                                        break;
                                    case "error":
                                        notificationText.html(response['message']);
                                        notificationElement.attr('class', 'error notification');
                                        break;
                                    case "captcha": // Problem with the recaptcha verification
                                        notificationText.html(response['message']);
                                        notificationElement.attr('class', 'error notification');
                                        grecaptcha.reset();
                                        break;
                                }

                            },
                            error: function(err) {
                                console.log(err);
                                responsePending = false;
                                notificationText.html('Unknown server error occurred. Please try later');
                                notificationElement.attr('class', 'error notification');
                                grecaptcha.reset();
                            }
                        });
                        responsePending = true;
                    }
	            });
            });

		</script>
	</head>

	<body>
		<?php require_once '../includes/navbar.include.php'; ?>
        <div id="notifications">
            <div class="notification">
                <a href="javascript:;" class="close-notification" title="Close"></a>
                <div class="message"></div>
            </div>
        </div>
		<div id="score-container">
			<div>
				<img src="../images/maze/zip.png">
				<span id="finders-folder-score">0</span>/1
			</div>
			<div>
				<img src="../images/maze/file.png">
				<span id="item-score">0</span>/4
			</div>
		</div>
		<div id="instructions" class="centered-box">
			<img src="../images/maze/move_instructions.png" alt="Move using the W, A, S, and D keys"/>
			<img src="../images/maze/look_instructions.png" alt="Look using the mouse or the arrow keys"/>
			<button type="button" onclick="hideInstructions()">got it!</button>
		</div>
		<div id="end-container" class="centered-box">
			<p>
				You found the Finder's Folder! <span>A <code>.zip</code> containing 4 files can now be downloaded to your computer.</span>
			 	Each of these files was uploaded by someone else who found the Finder's Folder. Now its your turn to upload a file so others can find it in the maze. File size limit is 3MB.
			</p>
            <form class="zip-file-upload" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="form-token" id="form-token" value="<?= $_SESSION['token'] ?>"/>
                <div class="file-upload-input-container">
                    <textarea name="comment" placeholder="Comment"></textarea>
                    <div class="recaptcha-box">
                        <div class="g-recaptcha" data-sitekey="6LcD0g0UAAAAAAdXw5uCpLsF8jUzHS0dZ7OcShR9"></div>
                    </div>
                    <label for="end-file">File</label>
                    <input type="file" name="end" id="end-file">
                </div>
            </form>
            <button id="download-zip">Download ZIP</button>
            <button id="submit">Upload</button>
            <button id="no-submit">No thanks</button>
		</div>

        <div id="file-container" class="centered-box">
            <span class="nameBlock"><span class="name">Anonymous</span></span>
            <span class="fileNum">File #<span id="file-number"></span>  </span>
            <div id="file-content-container">
                <img id="file-img-content" src=""/>
            </div>
            <span id="file-name"></span>
            <div class="comment-content"></div>
            <div class="button-container">
                <div class="btn-controls">
                    <button id="download-file">Download</button>
                    <button id="close-file-container">Close</button>
                </div>
            </div>
        </div>

		<div id="blocker">
			<p style="color: #fff">
				3DMaze downloads files to your computer when you pick up items. <br>
				To opt-out of this feature
				<span id="opt-out" onclick="optOutFileDownload()">click here</span>.
			</p>
			<progress value="0" max="100"></progress>
		</div>

		<script>
			// Globals
			var element = document.body; // Used for pointer lock
            var maze3D;
            var scene = new THREE.Scene();
			var progress = $('progress');
			var instructions = $("#instructions");
			var file_container = $("#file-container");
			var file_number = $("#file-number");
			var heightSubtractor = $('#navbar').height();
            var download_file = $("#download-file");
            var posts = $("ul#posts");
            var comment_content = $('.comment-content');
            var file_name = $('#file-name');
            var endContainer = $('#end-container');
            var notificationElement = $(".notification");
            var notificationText = $(".message");
            var stats = new Stats();
            stats.showPanel(0); // 0: fps, 1: ms, 2: mb, 3+: custom
            document.body.appendChild(stats.dom);

            download_file.click(function() {
                window.open(window.location.protocol + '//' + window.location.host + "/api/download_file.php?file=" + $(this).attr("data-file"));
            });

            $('#download-zip').click(function() {
                window.open(window.location.protocol + '//' + window.location.host + "/api/download_zip.php");
            });

            $('#close-file-container').click(function() {
                $('#file-content-container').empty();
                file_container.hide();
            });

            $(".close-notification").click(function() {
                notificationElement.attr("class", "notification");
            });

            var isLoaded = false;
			var isPointerLocked = false;

            // Wait for maze to load
            document.addEventListener('mazeloaded', MazeLoaded);

            function MazeLoaded(e) {
                isLoaded = true;
                $('#blocker').remove();
                showInstructions();
                $('#score-container').show();
                // Starts game
                maze3D.character.setEnabled(true);
            }

			// Get maze data
			$.ajax({
				url: "/api.php",
				type: "get",
				dataType: "json",
				error: function(err) {
					//console.log(err);
				},
				success: function(response) {
					var block3Dsize = 5;
					var mazeObj = response.data[0];
					maze3D = new Maze3D(scene, mazeObj, block3Dsize, "../images/maze/textures/", "../models/");
                    maze3D.init();

                    // Animation frame to animate progress bar
                    updateLoadProgress();

                    // Bind resize event
                    THREEx.WindowResize(maze3D.renderer, maze3D.camera, heightSubtractor);

                    // Bind lock pointer event
                    var domElement = maze3D.renderer.domElement;
                    domElement.onclick = lockPointer;
                    document.body.appendChild(domElement);

                    // Listen to On Hit events from Maze3D.js class
                    document.addEventListener('onhit', OnHit);
                    document.addEventListener('onfileloaded', OnFileLoaded);
                    document.addEventListener('onendreached', OnEndReached);

                    // Wait till all locations have been loaded before animating
                    document.addEventListener('locationsloaded', function() {
                        animate()
                    });
				}
			});

            /**
             * Handler for event invoked by Maze3D when character hits the end
             **/
            function OnEndReached(e) {

                var havePointerLock = 'pointerLockElement' in document ||
                    'mozPointerLockElement' in document ||
                    'webkitPointerLockElement' in document;

                if (havePointerLock) {
                    // Ask the browser to release the pointer
                    document.exitPointerLock = document.exitPointerLock ||
                        document.mozExitPointerLock ||
                        document.webkitExitPointerLock;

                    // Ask the browser to release the pointer
                    document.exitPointerLock();
                    incrementScore('#finders-folder-score');
                }

                endContainer.show();
            }

            /**
             * Displays a file window with a download button
             * Event invoked by Maze3D class when a character hits a file
             * @param e {event} file (file name), fileNumber
             */
			function OnHit(e) {
                incrementScore('#item-score');
                download_file.attr("data-file", ""); // Reset file attribute in case someone downloads the wrong file while the other is loading
                download_file.html("Loading...");
                file_container.show();
            }

            /**
             * Event handler for when file contents are loaded from Maze3D class
             * Method is called after API responds for retrieving file name
             * Method isn't called if downloads are disabled
             * Retrieves properties: fileNumber {int}, fileobject {object}
             * */
            function OnFileLoaded(e) {
                file_number.html(e.detail.fileNumber);
                file_name.html(e.detail.fileobject.name);
                // File is an image
                var file_link = "/uploads/findersfolder/" + e.detail.fileobject.file;
                if (e.detail.fileobject.file.match(/\.(jpg|jpeg|png|gif)$/)) {
                    $('#file-content-container').html(
                        "<a href='"+file_link+"' target='_blank'><img src='"+file_link+"'/></a>"
                    )
                } else if (e.detail.fileobject.file.match(/\.(ogg|mp3)$/)) { // File is audio
                    $('#file-content-container').html(
                        "<audio controls>" +
                        "<source src="+file_link+">" +
                        "Your browser does not support the audio element." +
                        "</audio>")
                } else if (e.detail.fileobject.file.match(/\.(webm|mp4)$/)) { // File is video
                    $('#file-content-container').html(
                        "<video controls>" +
                        "<source src="+file_link+">" +
                        "Your browser does not support the video element." +
                        "</video>")
                } else {
                    $('#file-content-container').empty();
                }

                // File
                download_file.html("Download File");
                download_file.attr("data-file", e.detail.fileobject.file);
                comment_content.html(e.detail.fileobject.comment);
            }

            /**
             * Updates progress until the maze has been loaded
             **/
			function updateLoadProgress() {
                if (isLoaded) return; // Stops recursive loop
                requestAnimationFrame(updateLoadProgress);
                progress.val(maze3D.getPercentLoaded());
            }

            /**
             * Handles animation/draw frames
             **/
			function animate() {
                stats.begin();
                maze3D.update(maze3D.clock.getDelta());
                maze3D.renderer.render(scene, maze3D.camera);
                stats.end();
                requestAnimationFrame(animate);
			}

			function optOutFileDownload(){
				maze3D.setDownloadsEnabled(false);
				$('#blocker p').css({ color: $('#blocker').css('color')});
				$('#blocker p span').css({ cursor: 'default'});
				$('#end-container span').css({textDecoration: 'line-through'});
			}

			function incrementScore(scoreSelector) {
				var score = parseInt($(scoreSelector).html());
				score += 1;
				$(scoreSelector).html(score);
			}

			function showInstructions() {
				instructions.show();
			}

			function hideInstructions() {
				instructions.hide();
			}

			function lockPointer() {
				var havePointerLock = 'pointerLockElement' in document ||
								   'mozPointerLockElement' in document ||
								'webkitPointerLockElement' in document;
				
				if (!havePointerLock) return;

				// Ask the browser to lock the pointer
				element.requestPointerLock = element.requestPointerLock ||
										  element.mozRequestPointerLock ||
									   element.webkitRequestPointerLock;

				// Ask the browser to lock the pointer
				element.requestPointerLock();
				
				// Hook pointer lock state change events
				document.addEventListener(      'pointerlockchange', pointerLockChange, false);
				document.addEventListener(   'mozpointerlockchange', pointerLockChange, false);
				document.addEventListener('webkitpointerlockchange', pointerLockChange, false);

                /**
                 * Event handler for the pointer lock status
                 */
				function pointerLockChange() {
                    if (document.pointerLockElement       === element ||
                        document.mozPointerLockElement    === element ||
                        document.webkitPointerLockElement === element)
                    {
						// Pointer was just locked, enable the mousemove listener
						document.addEventListener("mousemove", mouseMove, false);
                        maze3D.character.setEnabled(true);
						isPointerLocked = true;
					} else {
						// Pointer was just unlocked, disable the mousemove listener
						document.removeEventListener("mousemove", mouseMove, false);
						maze3D.character.setEnabled(false);
						isPointerLocked = false;
					}

					var opacity = isPointerLocked ? 1 : 0;
					$('.navbar-insert span').css({opacity: opacity});
				}

                /**
                 * Handles movement of character
                 * @param e Event
                 */
				function mouseMove(e) {
				    maze3D.character.mouseMove(e);
                }
			}
		</script>
	</body>
</html>