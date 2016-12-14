/*
Copyright (c) Andrew Trice & tomazy <https://github.com/tomazy>
Major Revisions by Brannon Dorsey

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

function SketchPad( canvasID, brushImagePath, brushLoadCallback ) {
	this.points = [];
    this.renderFunction = this.updateCanvasByBrush;
	this.brush = new Image();
	this.brush.src = brushImagePath;
    this.scale = 1;
	this.brush.onload = brushLoadCallback; // Calls initBrush and setEnabled inside callback
	this.touchSupported = ('ontouchstart' in window);
	this.canvas = $("#"+canvasID);
	this.context = this.canvas.get(0).getContext("2d");	
	this.context.strokeStyle = "#000000";
  //  this.context.globalCompositeOperation = 'destination-out';
	this.context.lineWidth = 10;
	this.lastMousePoint = {x:0, y:0};
    
	if (this.touchSupported) {
		this.mouseDownEvent = "touchstart";
		this.mouseMoveEvent = "touchmove";
		this.mouseUpEvent = "touchend";
	}
	else {
		this.mouseDownEvent = "mousedown";
		this.mouseMoveEvent = "mousemove";
		this.mouseUpEvent = "mouseup";
	}
	this.enabled = true;
	this.canvas.bind( this.mouseDownEvent, this.onCanvasMouseDown() );
}

/**
 * Initializes brush properties
 */
SketchPad.prototype.initBrush = function() {
    this.scaledWidth = this.brush.width * this.scale;
    this.scaledHeight = this.brush.height * this.scale;
    this.halfBrushW = this.scaledWidth/2;
    this.halfBrushH = this.scaledHeight/2;
};

/**
 * Updates brush scale properties
 * @param scale {float|int}
 */
SketchPad.prototype.updateScale = function(scale) {
    this.scale = scale;
    this.initBrush();
};

/**
 *
 * @param imgW
 * @param imgH
 * @param maxW
 * @param maxH
 * @returns {number}
 */
SketchPad.prototype.scalePreserveAspectRatio = function(imgW,imgH,maxW,maxH){
    return(Math.min((maxW/imgW),(maxH/imgH)));
};

SketchPad.prototype.setEnabled = function(bool){
	this.enabled = bool;
};

SketchPad.prototype.isEnabled = function(){
	return this.enabled;
};

SketchPad.prototype.onCanvasMouseDown = function () {
	var self = this;
	return function(event) {
		if(self.enabled){
	        self.preOnCanvasMouseDown.call();
	        
			self.mouseMoveHandler = self.onCanvasMouseMove();
			self.mouseUpHandler = self.onCanvasMouseUp();

			$(document).bind( self.mouseMoveEvent, self.mouseMoveHandler );
			$(document).bind( self.mouseUpEvent, self.mouseUpHandler );
			
			self.updateMousePosition( event );
	        self.points.push([self.lastMousePoint.x, self.lastMousePoint.y]);
			self.renderFunction( event );
		}
	}
};

SketchPad.prototype.onCanvasMouseMove = function () {
	var self = this;
	return function(event) {
		if(self.enabled){
			self.renderFunction( event );
	     	event.preventDefault();
     	}
    	return false;
	}
};

SketchPad.prototype.onCanvasMouseUp = function (event) {
	var self = this;
	return function(event) {
        self.points = [];
		$(document).unbind( self.mouseMoveEvent, self.mouseMoveHandler );
		$(document).unbind( self.mouseUpEvent, self.mouseUpHandler );
		
		self.mouseMoveHandler = null;
		self.mouseUpHandler = null;
	}
};

SketchPad.prototype.updateMousePosition = function (event) {
 	var target;
	if (this.touchSupported) {
		target = event.originalEvent.touches[0]
	}
	else {
		target = event;
	}

	var offset = this.canvas.offset();
	this.lastMousePoint.x = target.pageX - offset.left;
	this.lastMousePoint.y = target.pageY - offset.top;
};



SketchPad.prototype.updateCanvasByLine = function (event) {

	this.context.beginPath();
	this.context.moveTo( this.lastMousePoint.x, this.lastMousePoint.y );
	this.updateMousePosition( event );
	this.context.lineTo( this.lastMousePoint.x, this.lastMousePoint.y );
	this.context.stroke();
};

SketchPad.prototype.updateCanvasByBrush = function (event) {
	var start = { x:this.lastMousePoint.x, y: this.lastMousePoint.y };
	this.updateMousePosition( event );
    this.points.push([this.lastMousePoint.x, this.lastMousePoint.y]);
	var end = { x:this.lastMousePoint.x, y: this.lastMousePoint.y };
	this.drawLine( start, end );
};

SketchPad.prototype.drawLine = function (start, end) {
    var dx = end.x - start.x;
    var dy = end.y - start.y;
	var distance = parseInt(Math.sqrt(dx*dx + dy*dy));

    var x, y;

	if ( distance > 20 ) {
         
        var s = Smooth(this.points,{ 
 			method: 'cubic',
    		clip: 'clamp',
    		cubicTension: 0
        });
        
        for (var t = this.points.length - 2; t <= this.points.length - 1; t+= (1/distance)) {
          var point = s(t);
          x = point[0] - this.halfBrushW;
          y = point[1] - this.halfBrushH;
			this.context.drawImage(this.brush, x, y , this.scaledWidth, this.scaledHeight);
        }
    } else if (distance > 0 && distance <= 20) {
      // console.log('slow:' + distance);
		var sin_a = ( end.y - start.y ) / distance;
		var cos_a = ( end.x - start.x ) / distance;
        
		for ( var z=0; z <= distance - 1; z++ ){
			x = start.x + ( cos_a * z ) - this.halfBrushW;
			y = start.y + ( sin_a * z ) - this.halfBrushH;
			this.context.drawImage(this.brush, x, y , this.scaledWidth, this.scaledHeight);

        }

	} else {
      	this.context.drawImage(this.brush, start.x - this.halfBrushW, start.y - this.halfBrushH, this.scaledWidth, this.scaledHeight);
    }
};

SketchPad.prototype.toString = function () {

	var dataString = this.canvas.get(0).toDataURL("image/png");
	var index = dataString.indexOf( "," )+1;
	dataString = dataString.substring( index );
	
	return dataString;
};

SketchPad.prototype.toDataURL = function () {
	return this.canvas.get(0).toDataURL("image/png");
};

SketchPad.prototype.clear = function () {

	var c = this.canvas[0];
	this.context.clearRect( 0, 0, c.width, c.height );
};

SketchPad.prototype.preOnCanvasMouseDown = function() {
   //override this...
   // console.log('hi');
};
			
