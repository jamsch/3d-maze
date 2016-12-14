<!DOCTYPE html>
<html>
	<head>
		<?php 
            require_once '../includes/config.include.php';
            require_once '../includes/head.include.php';
		?>
		<link rel="stylesheet" type="text/css" href="../styles/styles.css">
		<link rel="stylesheet" href="../styles/farbtastic.css" />
		<script src="../scripts/canvas/sketcher/smooth.js"></script>
		<script src="../scripts/helpers.js"></script>
		<script src="../scripts/jquery-3.1.1.min.js"></script>
		<script src="../scripts/farbtastic.min.js"></script>
		<script src="../scripts/canvas/sketcher/classes/SketchPad.js"></script>
		<script src="../scripts/canvas/sketcher/classes/MarkerMaker.js"></script>
		<script src="../scripts/canvas/sketcher/classes/WallDrawing.js"></script>
		<script src="../scripts/canvas/sketcher/classes/WallSegment.js"></script>
		<script>
			var hostname = "<?=$HOSTNAME?>";
			var initImageIndex = undefined;
			var initImageOffset = undefined;
			var numbImages = 720;
			var imageSize = 256;

			var initColor = '#'+Math.floor(Math.random()*16777215).toString(16);

			<?php if (isset($_GET["drawing"]) && is_numeric($_GET["drawing"])): ?>
                initImageIndex = <?= $_GET["drawing"]?>;
                if (initImageIndex <= 1)
                    initImageIndex = 2;
                else if (initImageIndex >= numbImages)
                    initImageIndex = numbImages - 1;
			<?php endif; ?>

			<?php if (isset($_GET["offset"]) && is_numeric($_GET["offset"])): ?>
                initImageOffset = <?= $_GET["offset"] ?>;
                // Validate offset
                if (initImageOffset >= imageSize / 2 || initImageOffset <= - imageSize / 2) {
                    initImageOffset = 0;
                }
			<?php endif; ?>
			
			<?php if (isset($_GET["color"]) && !empty($_GET["color"])): ?>
                var color = '<?= "#" . $_GET["color"] ?>';
                // Check if color is a valid color hex
                if (/^#[0-9A-F]{6}$/i.test(color)) initColor = color;
			<?php endif; ?>
		
		</script>
		<script src="../scripts/canvas/sketcher/sketcher.js">//this is the script that does stuff</script>
		<script type="text/javascript">
			$(document).ready(function(){
				//fade in instructions
				setTimeout(function(){
					$(".instructions").addClass('instructions-show');
				}, 200);
			});
        </script>
	</head>

	<body>
		<?php require_once '../includes/navbar.include.php'; ?>
		<div class="content">
			<p class="instructions">
			    Draw on the walls of the maze. Be careful, there is no eraser!
			</p>
			  
	       	<ul class="tools">
                <input id="draw_size" type="range" min="0.1" max="3" step="0.1" value="1">
	          	<div id="tool_button" title="tools" class="draw_button"></div>
	            <div id="color_swatch" title="color picker"></div>
	            <div id="colorpicker" style="position:absolute;"></div>
	        </ul>
	        <div class="sketch-container">
	        	<div class="loading">
	        		<img src="../images/sketcher/loading_2.gif">
	        	</div>
				<canvas id="sketch" class="drawing" width="768" height="256"></canvas>
			</div>

			<p style="text-align: center; padding-top: 20px">Finished drawing? Try and <a href="../play">find</a> it.</p>
			
		</div>
	</body>
</html>