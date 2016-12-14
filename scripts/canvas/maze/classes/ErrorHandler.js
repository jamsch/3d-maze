function ErrorHandler(){
	this._errors = [];
	this._errorHTMLParent = $("div.error-box");
}

//----------------------------------------------------------------
//PUBLIC

ErrorHandler.prototype.reset = function(){
	this._errors = [];
};

ErrorHandler.prototype.clearErrorBox = function(containerSelector){
	var container = $(containerSelector);
	this._errorHTMLParent.children("ul").remove(); //clear content
};

ErrorHandler.prototype.outputErrors = function(){
	var container = this._errorHTMLParent.append("<ul></ul>").children("ul");
	for(var i = 0; i < this._errors.length; i++){
		var html = "<li>" + this._errors[i] + "</li>";
		container.append(html);
	}
};

//boolean that checks if errors exist and returns true if they do
ErrorHandler.prototype.checkErrors = function(maze, locations){
	this._checkLocationvalidity(maze, locations);
	this._checkWallsConnected(maze);

	if (this._errorsExist()) return true;
	//check for more errors
		/*
		for (var key in locations) {
			if(key == 'begin') continue; //skip begin because begin point doesnt need to be compared to itself
			var solver = new MazeSolver(maze,
										locations['begin'].mazeX,
										locations['begin'].mazeY,
										locations[key].mazeX,
										locations[key].mazeY);

			if(!solver.isSolvable()){

				//don't use the key names for front end stuff...
				var locationName;
				if(key == 'begin') locationName = "Start";
				else if(key == 'end') locationName = "End";
				else{
					locationName = key.replace("file", "Item ");
				}
				this._addError(locationName + " cannot be reached");
			}
		}

	 */
	return this._errorsExist(); //no errors were found
};

//prints a maze to the console. Used for debugging.
ErrorHandler.prototype.printMaze = function(maze)
{
    for (var y = 0; y < maze[0].length; y++){
        var row = "";
        for(var x = 0; x < maze.length; x++){
            row += maze[y][x].toString();
        }
        console.log(row);
    }
    console.log();
}

//----------------------------------------------------------------
//PROTECTED

ErrorHandler.prototype._errorsExist = function(){
	return (this._errors.length > 0) ? true : false;
}

ErrorHandler.prototype._addError = function(message){
	this._errors.push(message);
}

ErrorHandler.prototype._checkLocationvalidity = function(maze, locations){

	var maze = this.getCopy(maze);
	var errorMessage = "this location is not allowed";
	for(var key in locations){
		var location = locations[key];

		//make sure the location isn't on top of
		//other locations
		for(var key2 in locations){

			if(location.mazeX == locations[key2].mazeX &&
			   location.mazeY == locations[key2].mazeY &&
			   key != key2){
				this._addError(errorMessage);
				return;
			}
		}
	
		//make sure the location isn't on a wall
		if(maze[location.mazeY][location.mazeX] == 1){
			this._addError(errorMessage);
			break;
		}
	}
};

//uses flood fill algorithm with recursion to check if all
//maze walls are connected
ErrorHandler.prototype._checkWallsConnected = function(maze){
	
	var maze = this.getCopy(maze);
	this._floodFill(maze, 0, 0);

	var islandsExist = false;
	for(var y = 0; y < maze.length; y++){

		for(var x = 0; x < maze[0].length; x++){
			if(maze[y][x] == 1){
				islandsExist = true;
				break;
			}
		}
		if(islandsExist) break;
	}

	if(islandsExist) this._addError('All walls must be connected<br>No "islands" of any size are allowed');
};

ErrorHandler.prototype._floodFill = function(maze, x, y){

	var target = 1;
	var replacement = 2;
	
	if(y > 0)									      var up = maze[y - 1][x];
    if(y > 0 && x < maze[0].length - 1) 		      var upRight = maze[y - 1][x + 1];
    if(x < maze[0].length - 1) 					      var right = maze[y][x + 1];
    if(y < maze.length - 1 && x < maze[0].length - 1) var rightDown = maze[y + 1][x + 1];
    if(y < maze.length - 1) 					      var down = maze[y + 1][x];
    if(y < maze.length - 1 && x > 0) 			      var downLeft  = maze[y + 1][x - 1];
    if(x > 0) 									      var left = maze[y][x - 1];
    if(y > 0 && x > 0) 							      var leftUp  = maze[y - 1][x - 1];

    maze[y][x] = replacement;

    if(typeof up !== 'undefined' &&
       up == target) this._floodFill(maze, x, y - 1);
    if(typeof upRight !== 'undefined' &&
       upRight == target) this._floodFill(maze, x + 1, y - 1);
    if(typeof right !== 'undefined' &&
       right == target) this._floodFill(maze, x + 1, y);
    if(typeof rightDown !== 'undefined' &&
       rightDown == target) this._floodFill(maze, x + 1, y + 1);
    if(typeof down !== 'undefined' &&
       down == target) this._floodFill(maze, x, y + 1);
    if(typeof downLeft !== 'undefined' &&
       downLeft == target) this._floodFill(maze, x - 1, y + 1);
    if(typeof left !== 'undefined' &&
       left == target) this._floodFill(maze, x - 1, y);
    if(typeof leftUp !== 'undefined' &&
       leftUp == target) this._floodFill(maze, x - 1, y - 1);
}

//because 2D arrays are passed by reference this function copies the maze
//same function as MazeSolver.prototype.getCopy()
ErrorHandler.prototype.getCopy = function(maze){
    var arrayToReturn = [];
    for(var i = 0; i < maze.length; i++){
        arrayToReturn[i] = maze[i].slice();
    }
    return arrayToReturn;
}

