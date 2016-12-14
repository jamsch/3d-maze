function WallDrawing(hostname, canvas, wallSize, numImages, initImageIndex, initImageOffset) {
    this.initImageIndex = typeof initImageIndex !== 'undefined' && initImageIndex <= numImages ? initImageIndex : Math.ceil(Math.random()*numImages-1);
	if (this.initImageIndex <= 1) this.initImageIndex = 2;
	else if (this.initImageIndex >= numImages) this.initImageIndex = numImages - 1;
	this.wallSize = wallSize;
	this.wallSegments = [];
	this.canvas = canvas;
	this.context = this.canvas.getContext('2d');
	this.numInitImagesToLoad = 8; //must be odd number
	
	this.initWallSegments = [];

	var startX = this.canvas.width/2 - this.initImageIndex * this.wallSize + this.wallSize / 2;
	if(typeof initImageOffset !== "undefined") startX += initImageOffset;
	var x = startX;

	for (var i = 0; i < numImages; i++) {
		var shouldLoad = (i > this.initImageIndex - this.numInitImagesToLoad/2 &&
		   i < this.initImageIndex + this.numInitImagesToLoad/2);

		this.wallSegments[i] = new WallSegment(hostname, this.context, x, 0, this.wallSize, i + 1, shouldLoad);
		if(shouldLoad){
			this.initWallSegments.push(this.wallSegments[i]);
		}
		x += this.wallSize;
	}
}

WallDrawing.prototype.getInitImageIndex = function() {
	return this.initImageIndex;
};

WallDrawing.prototype.updateImages = function() {
	
	var visibleWalls = this._getVisibleWalls();
	for (var i = 0; i < visibleWalls.length; i++){
		var visibleWall = visibleWalls[i];
		if (visibleWall.needsUpdate()) {
			visibleWalls[i].updateImage();
		}
	}
	//console.log("drawing: " + this.getMiddleWall().imageIndex);
};

// Called onMouseUp if dragging tool was enabled.
WallDrawing.prototype.loadImages = function(previousMouseX, mouseX) {

	//the number of images to keep loaded to each
	//side of the frame
	var imagesToBuffer = 5;
	var visibleWalls = this._getVisibleWalls();

	// Canvas dragged right, load left
	if (previousMouseX < mouseX) {
		var leftVisibleWall = this._getLeftMostWall(visibleWalls);
		var leftWallIndex = leftVisibleWall.imageIndex - 1;

		//if we are not too close to the left most image

		if (leftWallIndex >= imagesToBuffer) {
			for (var i = leftWallIndex; i > leftWallIndex - imagesToBuffer; i--) {
                var wallSegment = this.wallSegments[i];
                if(!wallSegment.isLoaded() && !wallSegment.isLoading()) {
                	wallSegment.loadImage();
                    //console.log('I loaded an image to the left');
                }
            }
		} else {
            for (var i = leftWallIndex; i > 0; i--) {
                var wallSegment = this.wallSegments[i];
                if(!wallSegment.isLoaded() && !wallSegment.isLoading()) {
                    wallSegment.loadImage();
                    //console.log('I loaded an image to the left');
                }
            }
		}
		
	} else if(previousMouseX > mouseX) { //images dragged left, load right
		var rightVisibleWall = this._getRightMostWall(visibleWalls);
		var rightWallIndex = rightVisibleWall.imageIndex - 1;

		//if we are not too close to the right most image
		if(rightWallIndex <= this.wallSegments.length - imagesToBuffer) {
			for (var i = rightWallIndex; i < rightWallIndex + imagesToBuffer; i++) {
				var wallSegment = this.wallSegments[i];
				if (!wallSegment.isLoaded() && !wallSegment.isLoading()){
					wallSegment.loadImage();
					//console.log('I loaded an image to the right');
				}
			}
		}
	}
};

WallDrawing.prototype.drag = function(previousMouseX, mouseX) {

	var canDrag = false;

	//if dragged wall right
	if (previousMouseX < mouseX) {
		if (this.wallSegments[0].x < 0) {

			//not too close to the first wall (used for loading gif) 
			if(this._getLeftMostWall(this._getVisibleWalls()).imageIndex > 2) {
				var middleWall = this.getMiddleWall();
				//if the two images to the left are loaded...
				if(this.wallSegments[middleWall.imageIndex - 1].isLoaded() &&
			       this.wallSegments[middleWall.imageIndex - 2].isLoaded())
				{
					canDrag = true;
				} else {
					return false;
                }
			} else canDrag = true;
		}
	} else if (previousMouseX > mouseX) { //if dragged left
		var lastWallSegment = this.wallSegments[this.wallSegments.length - 1];
		if (lastWallSegment.x + lastWallSegment.size > this.canvas.width) {
			//not too close to the last wall (used for loading gif) 
			if(this._getRightMostWall(this._getVisibleWalls()).imageIndex < this.wallSegments.length - 2){
				var middleWall = this.getMiddleWall();
				// if the two images to the right are loaded...
				if (this.wallSegments[middleWall.imageIndex].isLoaded() && this.wallSegments[middleWall.imageIndex + 1].isLoaded()) {
					canDrag = true;
				} else return false; //loading
			} else canDrag = true;
		}
	}

	if (canDrag) {
		this._walkWallSegments(function(wallSegment) {
			wallSegment.update(previousMouseX, mouseX);
		});
		this.display();
	}
};

WallDrawing.prototype.display = function() {
	this._walkWallSegments(function(wallSegment){
		wallSegment.display();
	});
};

WallDrawing.prototype.notifyNeedsUpdate = function(previousMouseX, mouseX) {
	this._walkWallSegments(function(wallSegment) {
		if(wallSegment.inside(previousMouseX) ||
		   wallSegment.inside(mouseX)) {
			wallSegment.notifyNeedsUpdate();
		}
	});
};

/**
 * Verifies that there are loading segments - for saving images in sketcher.js
 * @returns {boolean}
 */
WallDrawing.prototype.hasLoadingSegments = function() {
	var imagesToBuffer = 5;
    var visibleWalls = this._getVisibleWalls();
    var leftVisibleWall = this._getLeftMostWall(visibleWalls);
    var leftWallIndex = leftVisibleWall.imageIndex - 1;


    //if we are not too close to the right most image
	for (var i = leftWallIndex; i < leftWallIndex + imagesToBuffer && i < 1000; i++) {
        if (!this.wallSegments[i].isLoaded()) return true;
	}

    return false;
};

//returns true if initial images are loaded and checks 
//again if they arent
WallDrawing.prototype.initImagesLoaded = function() {

	var numLoaded = 0;
	for(i = 0; i < this.initWallSegments.length; i++) {
		var initWallSegment = this.initWallSegments[i];
		if(initWallSegment.isLoaded()) numLoaded++;
	}

	//if all initial images have been loaded...
    return numLoaded == this.initWallSegments.length;
};

WallDrawing.prototype.getMiddleWall = function() {
	var visibleWalls = this._getVisibleWalls();
	var middleWall = visibleWalls[0];
	for(var i = 1; i < visibleWalls.length; i++){
		var visibleWall = visibleWalls[i];
		var centerX = this.canvas.width / 2;
		//if visibleWall is closer to the center of the 
		//canvas than the current middleWall
		if (Math.min(
			Math.abs((visibleWall.x + visibleWall.size / 2) - centerX),
			Math.abs((middleWall.x + middleWall.size / 2) - centerX)
		) < Math.abs((middleWall.x + middleWall.size / 2) - centerX)){
			middleWall = visibleWall;
		}
	}
	return middleWall;
};

WallDrawing.prototype.getMiddleWallOffset = function() {
	var middleWall = this.getMiddleWall();
	return (middleWall.x + middleWall.size / 2) - (this.canvas.width / 2);
};


//------------------------------------------------------------------------
//PROTECTED FUNCTIONS

//returns array of all wallSegements that are inside the canvas frame
WallDrawing.prototype._getVisibleWalls = function() {
		
	var visibleWalls = [];
	this._walkWallSegments(function(wallSegment) {
		//if at least part of the wallSegment is in the canvas frame...
		if(wallSegment.x > 0 &&
		   wallSegment.x < this.canvas.width ||
		   wallSegment.x + wallSegment.size > 0 &&
		   wallSegment.x + wallSegment.size < this.canvas.width){
			visibleWalls.push(wallSegment);
		}
	});
	return visibleWalls;
};

WallDrawing.prototype._getLeftMostWall = function(wallSegmentsArray) {
	var leftMostWall = wallSegmentsArray[0];
	for(var i = 0; i < wallSegmentsArray.length; i++){
		var wallSegment = wallSegmentsArray[i];
		if(Math.min(wallSegment.x, leftMostWall.x) == wallSegment.x) leftMostWall = wallSegment;
	}
	return leftMostWall;
};

WallDrawing.prototype._getRightMostWall = function(wallSegmentsArray) {
	var rightMostWall = wallSegmentsArray[0];
	for(var i = 0; i < wallSegmentsArray.length; i++){
		var wallSegment = wallSegmentsArray[i];
		if(Math.max(wallSegment.x, rightMostWall.x) == wallSegment.x) rightMostWall = wallSegment;
	}
	return rightMostWall;
};

WallDrawing.prototype._walkWallSegments = function(fn) {
	for(var i = 0; i < this.wallSegments.length; i++){
		var needsBreak = fn(this.wallSegments[i]);
		if(needsBreak) break;
	}
};