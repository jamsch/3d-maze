<?php
	require_once '../includes/config.include.php';
    require_once '../includes/util.include.php';
	require_once '../includes/helpers.include.php';

	require_once '../includes/classes/api_builder_includes/class.API.inc.php';
	require_once '../includes/classes/api_builder_includes/class.Database.inc.php';

	// setup and instantiate the api
	require_once '../includes/api_columns.include.php';
	require_once '../includes/api_setup.include.php';

	// file validation include
	require_once '../includes/filevalidation.include.php';


	$query_array = array("limit" => 1,
						 "pretty_print" => false);

	if (!$results =  $api->get_json_from_assoc($query_array)) die("Database error");

    $json = json_decode($results);

    if (!empty($json->error)) die("Database error");

    $mazeData = json_encode($json->data[0]);
?>

<!DOCTYPE html>
<html>
	<head>
		<?php require_once '../includes/head.include.php'; ?>
		<link rel="stylesheet" type="text/css" href="../styles/styles.css">
		<script src="../scripts/helpers.js"></script>
		<script type="text/javascript" src="../scripts/jquery-3.1.1.min.js" >//load jquery</script>
		<script type="text/javascript" src="../scripts/canvas/kinetic-v4.7.0.min.js">//load kinetic</script>
		<script type="text/javascript" src="../scripts/canvas/maze/classes/MazeSolver.js"></script>
		<script type="text/javascript" src="../scripts/canvas/maze/classes/Block.js"></script>
		<script type="text/javascript" src="../scripts/canvas/maze/classes/Maze2D.js"></script>
		<script type="text/javascript" src="../scripts/canvas/maze/classes/Location.js"></script>
		<script type="text/javascript" src="../scripts/canvas/maze/classes/ErrorHandler.js"></script>
		<script type="text/javascript">
            var reloadTimeout;
            var reloadRate = 8 * 60; //in seconds

			$(document).ready(function() {
                var notificationElement = $(".notification");
                var notificationText = $(".message");

                $(".close-notification").click(function() {
                    notificationElement.attr("class", "notification");
                });

			    document.querySelector("#maze-form").addEventListener('submit', function(e) {
			        e.preventDefault();

                    notificationText.html("Processing");
                    notificationElement.attr('class','warning notification');

                    var form = e.target.elements;
                    if (!saveMaze()) { // Call function in canvas-maze.js
                        notificationText.html("There are problems in your maze. Please fix them before submitting");
                        notificationElement.attr('class','warning notification');
                        return;
                    }
                    $.ajax({
                        method: 'post',
                        url: '../api/update_maze.php',
                        data: $("#maze-form").serialize(),
                        success : function(reply) {
                            console.log(reply);
                            if (!reply.hasOwnProperty('status')) {
                                notificationText.html("Unknown error occurred");
                                notificationElement.attr('class','error notification');
                            }
                            switch(reply.status) {
                                case 'success':
                                    notificationText.html("Maze updated, go <a href='../play'>play</a>!");
                                    notificationElement.attr('class','success notification');
                                    break;
                                case 'error':
                                    notificationText.html(reply.message);
                                    notificationElement.attr('class','error notification');
                                    break;
                            }
                        },
                        error : function(err) {
                            console.log(err);
                            notificationText.html("Unknown error occurred");
                            notificationElement.attr('class','error notification');
                        }
                    });

                });

				// Fade in instructions
				setTimeout(function() {
                    $('.instructions').addClass('instructions-show');
				}, 200);

                var errorBox = $('.error-box');

				throb(errorBox, 500, 900);

				$('.error-box ul').hover(function(){
				    //console.log("I hovered!");
				});
                // Refresh page to in case maze has changed again
				document.addEventListener('mousemove', function(evt) {
			      setReloadTimeout();
			    });
			});

			function setReloadTimeout(){
			    if (typeof reloadTimeout !== 'undefined') clearTimeout(reloadTimeout);
			    reloadTimeout = setTimeout(function() {
			      location.reload();
			    },reloadRate * 1000);
			}
		</script>
	</head>

	<body>
		<?php require_once('../includes/navbar.include.php'); ?>
		<div class="content">
            <div id="notifications">
                <div class="notification">
                    <a href="javascript:;" class="close-notification" title="Close"></a>
                    <div class="message"></div>
                </div>
            </div>

			<p class="instructions">
			   Click to edit walls &amp; drag to move icons. <br/>
			   Press save below to update the <a href="../play">3D maze</a>.
			</p>

			<div class="maze-key">
				<div>
					<img src="../images/builder/begin.png"/>
					<span>start</span>
				</div>
				<div>
					<img src="../images/builder/file.png"/>
					<span>item</span>
				</div>
				<div>
					<img src="../images/builder/zip.png"/>
					<span>end</span>
				</div>
			</div>

			<div class="maze-container">
				<div id="container" class="canvas"></div>
				<div class="error-box"></div>
			</div>

			<script type="text/javascript">
				var mazeData = <?= $mazeData; ?>;
			</script>
			<script defer="defer" type="text/javascript" src="../scripts/canvas/maze/canvas-maze.js">//code for 2D editable maze</script>

			<form id="maze-form" method="post" action="index.php">
				<?php 
                    $input_columns = explode(", ", API::format_comma_delimited($columns));
                    unset($input_columns[0]);
                    unset($input_columns[1]);
                    $i = 1;
                    foreach ($input_columns as $column) {?>
                        <input <?php if(strstr($column, "file") !== false){ echo "id='file" . ceil($i/4) . "'"; $i++; }?>name="<?= $column ?>" type="hidden" value="">
				<?php }?>
				<input id="save" type="submit" value="save" class="button">
			</form>

			<p style="text-align: center">Finished editing the structure of the maze? You should <a href="../draw">draw</a> or <a href="../play">play</a>!</p>
		</div>

	</body>
</html>
