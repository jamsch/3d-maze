var sketcher = null;
var brush = null;
var wallDrawing;
var canvas;
var mousePressed = false;
var prevMousePos;
var dragToolEnabled = false;
var fb; //color picker
var reloadRate = 120; //in seconds
var reloadTimeout;
var drawingStartedWhileLoading = false;

function hideMenus() {
  $('#colorpicker').hide();
}

function installBrush(img, color) {
  brush = new MarkerMaker(img, color);
  sketcher.brush = brush;
  sketcher.renderFunction = sketcher.updateCanvasByBrush;
}

function setColor(color) {
  installBrush(sketcher.brush, color);
  $('#color_swatch').css('background-color',color);
}

function getMousePos(canvas, evt) {
  var rect = canvas.getBoundingClientRect();
  return {
    x: evt.clientX - rect.left,
    y: evt.clientY - rect.top
  };
}

function setReloadTimeout() {
    if(typeof reloadTimeout !== 'undefined') clearTimeout(reloadTimeout);
    reloadTimeout = setTimeout(function(){

      var url = location.href;
      var qIndex = url.indexOf('?');
      if(qIndex != -1) url = url.substring(0, qIndex);
      url = url + '?drawing=' + wallDrawing.getMiddleWall().imageIndex + '&color=' + fb.color.substring(1);
      url = url + '&offset=' + wallDrawing.getMiddleWallOffset() + '&t=' + new Date().getTime();
      window.location.href = '../redirect.php?url=' + encodeURIComponent(url);
    },reloadRate * 1000);
}

function bindEvents() {

     //color picker bindings
     $('#colorpicker').hide();
     $('#color_swatch').click(function() {
        sketcher.context.globalCompositeOperation = 'source-over';
        //hideMenus();
        $('#colorpicker').toggle();
     });

     //drag bindings
    $("#tool_button").click(function() {
        sketcher.setEnabled(dragToolEnabled); //this is done before the toggle
        dragToolEnabled = !dragToolEnabled;
        hideMenus();
        var sketch_button = $('#sketch');
        var tool_button = $('#tool_button');
        sketch_button.toggleClass('grab');
        sketch_button.toggleClass('drawing');
        tool_button.toggleClass('drag_button');
        tool_button.toggleClass('draw_button');
    });

    document.addEventListener('mousemove', function(evt) {
      setReloadTimeout();
    });

    canvas.addEventListener('mousemove', function(evt) {
        if (!mousePressed) return;

        var currentMousePos = getMousePos(canvas, evt);

        if (!dragToolEnabled) { // Drawing
            wallDrawing.notifyNeedsUpdate(prevMousePos.x, currentMousePos.x);
        } else { // Dragging...
            if (!wallDrawing.drag(prevMousePos.x, currentMousePos.x)) {
              $('.loading').show();
              setTimeout(function() { $('.loading').hide() }, 2000);
            } else {
              if (sketcher.isEnabled()) {
                  $('.loading').hide();
              }
            }
            wallDrawing.loadImages(prevMousePos.x, currentMousePos.x);
        }
        prevMousePos = currentMousePos;

    }, false);

    canvas.addEventListener('mousedown', function(evt) {
        prevMousePos = getMousePos(canvas, evt);
        mousePressed = true;
        
        if (dragToolEnabled) {
          sketcher.setEnabled(false);
          $('canvas').toggleClass('grabbing', true);
        } else if (wallDrawing.hasLoadingSegments()) {
          drawingStartedWhileLoading = true;
        } else {
          drawingStartedWhileLoading = false;
          sketcher.setEnabled(true);
          var currentMousePos = getMousePos(canvas, evt);
          wallDrawing.notifyNeedsUpdate(prevMousePos.x, currentMousePos.x);
        }
    }, false);

    // The mouse up event must be tied to the document, not the canvas
    document.addEventListener('mouseup', function(evt) {
        if (dragToolEnabled) {
          $('canvas').toggleClass('grabbing', false);
        } else if (!wallDrawing.hasLoadingSegments() && sketcher.isEnabled() && !drawingStartedWhileLoading) {
            //save images
            wallDrawing.updateImages();
        }

        mousePressed = false;
    }, false);
}

$(document).ready(function(e) {
	
    canvas = $("#sketch")[0];
	  sketcher = new SketchPad("sketch", "../images/sketcher/tip.png", function() {
          sketcher.setEnabled(false);
          sketcher.initBrush();
          wallDrawing = new WallDrawing(hostname, canvas, imageSize, numbImages, initImageIndex, initImageOffset);
          //start color picker at random color
          fb = $.farbtastic('#colorpicker', setColor);
          fb.setColor(initColor);

          // constantly check if first images are loaded
          // this is a kind of gross way to do it but oh
          // well at least I said it
          var intervalID = setInterval(function(){
              if (wallDrawing.initImagesLoaded()) {
                  sketcher.setEnabled(true);
                  $('.loading').hide();
                  loading = false;
                  clearInterval(intervalID);
              }
          }, 100);

          setReloadTimeout();
          bindEvents();
    });

    // Brush size change
    $(document).on('change', '#draw_size', function() {
        sketcher.updateScale($('#draw_size').val());
    });



    sketcher.preOnCanvasMouseDown = function(){
       hideMenus();
    }    
});
