var stage = new Kinetic.Stage({
	container: 'container',
	width: 800,
	height: 800
});
var layer = new Kinetic.Layer();

//-----------------------------------------------------------------------

var maze2D  = new Maze2D(JSON.parse(mazeData.maze), stage.getWidth());
maze2D.initLocations(mazeData, function(){
    maze2D.display(layer);
    bindEvents();
    stage.add(layer);
});

//-----------------------------------------------------------------------

function bindEvents() {
    // Bind events for each block...
    for(var y = 1; y < maze2D.blocks.length-1; y++){
        for(var x = 1; x < maze2D.blocks[0].length-1; x++){

            // on click
            maze2D.blocks[y][x].rect.on('click', function(){
                maze2D.toggleBlock(this.index, layer);
                maze2D.update();
                saveMaze();
            });
            
        }
    }

    // Bind events for locations
    for (var key in maze2D.locations) {
        var locationRect = maze2D.locations[key].rect;
        locationRect.on('dragend', function(){ 
            maze2D.recalculateLocation(this.index);
            saveMaze();
        });
    }
}

function saveMaze() {
    var errorHand = new ErrorHandler(); 
    errorHand.clearErrorBox();

    if (errorHand.checkErrors(maze2D.data, maze2D.locations)) {
       errorHand.outputErrors();
       return false;
    }

    errorHand.reset();
    maze2D.save();
    return true;
}
