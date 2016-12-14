function WallSegment(hostname, context, x, y, size, imageIndex, bShouldLoad){
	this.hostname = hostname;
	this.context = context;
	this.x = x;
	this.y = y;
	this.size = size;
	this.imageIndex = imageIndex;
	this.image = null;
	this._isLoaded = false;
	this._isLoading = false;
	this._needsUpdate = false;
	this.filename = zeroPad(this.imageIndex, 4) + '.png';
	this.imageURL = this.hostname + '/images/maze/textures/' + this.filename;
	if (bShouldLoad) {
		this.loadImage();
	}
}

WallSegment.prototype.update = function(previousMouseX, mouseX){
	this.x += mouseX - previousMouseX;
};

WallSegment.prototype.display = function(){
	if(this.isLoaded()){
		this.context.drawImage(this.image, this.x, this.y, this.size, this.size);
	}
};

/**
 * Updates and saves image
 */
WallSegment.prototype.updateImage = function() {
	var self = this;

	//create two memory-only canvas
	var tempCanvas = this._createCanvas();
	var tempContext = tempCanvas.getContext('2d');
	var tempCanvas2 = this._createCanvas();
	var tempContext2 = tempCanvas2.getContext('2d');

	tempContext.drawImage(this.image, 0, 0); //draw the old image to the first mem-only canvas

	//get the new image with all changes since the last drag (taken from the visible canvas)
	var newImageData = this.context.getImageData(this.x, this.y, this.size, this.size);
	tempContext2.putImageData(newImageData, 0, 0); //place the new image in the second mem-only canvas
	var newImageURL = tempCanvas2.toDataURL('image/png');
	var newImage = new Image();
	newImage.src = newImageURL;
	newImage.onload = function() {
		//draw the new image on top of the old image in the 
		//first mem-only canvas
		tempContext.drawImage(newImage, 0, 0);
		var dataURL = tempCanvas.toDataURL('image/png');

		//save the compilation as the new image
		var combinedImage = new Image();
		combinedImage.src = dataURL;
		combinedImage.onload = function(){
			self.image = combinedImage;
			self._needsUpdate = false;
			self.saveImage();
		}
	}
};
/**
 * Saves an image
 */
WallSegment.prototype.saveImage = function() {
	// pls no cheating :^(
	var encodedImage = encodeURIComponent(this.image.src);
	var data = {
		filename : this.filename,
		base64 : encodedImage
	};
	var self = this;
	$.ajax({
		url: self.hostname + '/api/saveimage.php',
		method: 'post',
		data: data,
		success: function(response) {
			console.log(response);
		},
		error: function(err){
			console.log(err);
		}
	});
	//}
};

WallSegment.prototype.loadImage = function() {
	var self = this;
	this.image = new Image();
	this.image.src = this.imageURL + '?' + new Date().getTime();
	this.image.height = this.size;
	this.image.width = this.size;
	this.image.onload = function() {
    	self._isLoaded = true;
    	self._isLoading = false;
    	//console.log("Loaded image " + self.imageIndex);
    	self.display();
	};
	this.image.onerror = function(){
		console.log("Error loading image " + self.imageIndex);
		//window.location.href = hostname + '/redirect.php?url=' + encodeURIComponent(hostname + '/draw/?load_error=true');
	};
	this._isLoading = true;

	//console.log("Loading image " + this.imageIndex);
};

WallSegment.prototype.isLoaded = function(){
	return this._isLoaded;
};

WallSegment.prototype.isLoading = function() {
	return this._isLoading;
};

WallSegment.prototype.notifyNeedsUpdate = function(){
	this._needsUpdate = true;
};

WallSegment.prototype.needsUpdate = function(){
	return this._needsUpdate;
};

WallSegment.prototype.inside = function(mouseX) {
    return (mouseX > this.x && mouseX < this.x+this.size)
};

//------------------------------------------------------------------------
//PROTECTED FUNCTIONS

WallSegment.prototype._createCanvas = function() {
	var tempCanvas = document.createElement('canvas');
	tempCanvas.width = this.size;
	tempCanvas.height = this.size;
	return tempCanvas;
}

