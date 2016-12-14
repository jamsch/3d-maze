function Maze3D(scene, mazeObj, block3DSize, pathToImagesFolder, pathToModelsFolder) {
	var self = this;
	this.scene = scene;
	this.pathToModelsFolder = pathToModelsFolder;
	this.pathToImagesFolder = pathToImagesFolder;
	this.fillerImage = "filler_image.png";
	this.imageType = ".png";
	this.date = new Date();
    this.clock = null;
    this.camera = null;
    this.character = null;
    this.renderer = null;
	this.mazeObj = mazeObj;
	this.data = JSON.parse(mazeObj.maze);
	this.width = this.data[0].length;
	this.height = this.data.length;
	this.block3DSize = block3DSize;
	this.textureData = JSON.parse(mazeObj.textureData);
    this.event = new Event('fileHit');
    this.locationsLoaded = 0;

	this._isLoaded = false;
	this._numbTexturesLoaded = 0; //number of textures currently loaded
	this._numTextures = 0; //max number of textures needed to load'
	this._downloadsEnabled = true;

	this.locations3D = [];

	//construct maze
	this.blocks3D = [];
	var xPos = 0;
	var yPos = 0;
	var zPos = 0;

	for (var z = 0; z < this.height; z++) {
		this.blocks3D[z] = [];
		for (var x = 0; x < this.width; x++) {
			//var index = x.toString()+","+z.toString();
			var state = this.data[z][x] == 1;
			var blockTextureData = this.textureData[z][x];
			var textureNames = [];
			for (var i = 0; i < blockTextureData.length; i++) {
				if (blockTextureData[i] == 0) {
                    textureNames[i] = 0;
                    continue;
                }
				textureNames[i] = this.pathToImagesFolder +
								  zeroPad(blockTextureData[i], 4) +
								  this.imageType +
								  '?time=' + this.date.getTime(); //included so that cached images aren't used
			}
			if (state) {
				this.blocks3D[z][x] = new Block3D(xPos, 
												  yPos, 
												  zPos, 
												  this.block3DSize, 
												  this.block3DSize, 
												  this.block3DSize, 
												  textureNames,
												  function() { //on material load call back
												  	self._numbTexturesLoaded++;
												  	//console.log('I loaded an image');
												  });
				this._numTextures += this.blocks3D[z][x].getNumbTextures();
			}
			xPos += this.block3DSize;
		}
		xPos = 0;
		zPos += this.block3DSize;
	}
	//console.log("Loading " + this._numTextures + " images");
	this._initLocations();
}

/**
 * Initializes Maze
 */
Maze3D.prototype.init = function(callback) {
    this.renderer = new THREE.WebGLRenderer({ antialias: true });
    this.camera = new THREE.PerspectiveCamera(60, window.innerWidth/window.innerHeight, 0.1, 26);
    this.clock = new THREE.Clock();

    // Renderer
    var heightSubtractor = $('#navbar').height();
    this.renderer.setSize(window.innerWidth, window.innerHeight - heightSubtractor);
    this.renderer.setClearColor( 0xe4e4e4, 1 );

    // Camera
    this.scene.add(this.camera);

    // Light
    var hemisphereLight = new THREE.HemisphereLight(0xffffff);
    hemisphereLight.position.set(1, 1, 1).normalize();
    this.scene.add(hemisphereLight);

    // Fog
    this.scene.fog = new THREE.Fog( 0xe4e4e4, 16, 26);

    this.addToScene();

    // Character
    // Character must be instantiated after maze3D is added to scene
    // var startPosition = new THREE.Vector3(4, 20, 5);
    var beginPosition = this.getBeginPosition();
    this.character = new CharacterController(this.scene, this.camera, beginPosition);
    this.character.registerCollisionObjects(this.getBlockMeshes(), this.getBlockSize());
};

/**
 * Update function. Runs in every animate() frame called in /play/index.php
 * @param delta
 */
Maze3D.prototype.update = function(delta) {
	var self = this;

	this._walkLocations(function(location) {
		if (!location.hit(self.character.body.position.x, self.character.body.position.z)) {
            location.update(delta);
            return;
        }

        if (!location.isGhosted()) {
            if (location.hasObject() && location.isLoaded()) {
                location.ghost();
                location.onHitFunc();
                location.setGhosted(true);
            }
        }
	});

	// Update character position
    this.character.update(delta);
};

// This should probably be init()
Maze3D.prototype.addToScene = function() {
	for (var z = 0; z < this.blocks3D.length; z++) {
		for (var x = 0; x < this.blocks3D[0].length; x++) {
			var block3D = this.blocks3D[z][x];
			if (typeof block3D !== 'undefined') {
				scene.add(block3D.cube);
			}
		}
	}	
};

// Finds the mesh objects of all block3Ds and returns them as an array
Maze3D.prototype.getBlockMeshes = function() {
	var blocks = [];
	this.walkBlocks(function(block) {
		blocks.push(block.cube);
	});
	return blocks;
};

Maze3D.prototype.getBlocks = function() {
	return this.blocks3D;
};

Maze3D.prototype.getBlockSize = function() {
	return this.block3DSize;
};

// Used to initialize character controller's position
Maze3D.prototype.getBeginPosition = function() {
	var x = this.locations3D['begin'].x;
	var y = this.locations3D['begin'].y;
	var z = this.locations3D['begin'].z;
	return new THREE.Vector3(x, y, z);
};

//walks through a function with all blocks
Maze3D.prototype.walkBlocks = function(walkFunction) {
	for (var z = 0; z < this.blocks3D.length; z++) {
		for (var x = 0; x < this.blocks3D[0].length; x++) {
			var block3D = this.blocks3D[z][x];
			//console.log(block3D);
			if (block3D != undefined) {
				walkFunction(block3D);
			}
		}
	}	
};

Maze3D.prototype.isLoaded = function(){
	return this._isLoaded;
};

Maze3D.prototype.getDownloadsEnabled = function(book) {
    return this._downloadsEnabled;
};

Maze3D.prototype.setDownloadsEnabled = function(bool){
	this._downloadsEnabled = bool
};

Maze3D.prototype.getPercentLoaded = function(){
	var percentLoaded = mapRange(this._numbTexturesLoaded, 0, this._numTextures, 0, 100);
	if (percentLoaded >= 99) {
        var evt = new Event('mazeloaded');
        document.dispatchEvent(evt);
	    this._isLoaded = true;
    }
	return percentLoaded;
};

Maze3D.prototype._walkLocations = function(walkFunction){
    for (var key in this.locations3D) {
        walkFunction(this.locations3D[key]);
    }
};

Maze3D.prototype._initLocations = function() {

    var self = this;

    // Begin
    this.locations3D['begin'] = new Location3D({
        scene: this.scene,
        y: 0,
        physical: false,
        color: 0x00ff00,
        x: this._toMaze3DCoords(JSON.parse(this.mazeObj.beginMazeX)),
        z: this._toMaze3DCoords(JSON.parse(this.mazeObj.beginMazeY))
    });

    // End - Findersfolder
    this.locations3D['end'] = new Location3D({
        scene: this.scene,
        physical: true,
        color: 0xff0000,
        x: this._toMaze3DCoords(JSON.parse(this.mazeObj.endMazeX)),
        y: 0,
        z: this._toMaze3DCoords(JSON.parse(this.mazeObj.endMazeY)),
        objPath: this.pathToModelsFolder + 'zip.obj',
        matPath: this.pathToModelsFolder + 'zip.mtl',
        onHitFunc: function () {
            self.OnEndReached();
        },
        onInit: function() {
            self.locationsLoaded++;
            self.OnLocationLoaded(self.locationsLoaded)
        }
    });

    // File 1
    this.locations3D['file1'] = new Location3D({
        scene: this.scene,
        physical: true,
        color: 0x686868,
        x: this._toMaze3DCoords(JSON.parse(this.mazeObj.file1MazeX)),
        y: 0,
        z: this._toMaze3DCoords(JSON.parse(this.mazeObj.file1MazeY)),
        objPath: this.pathToModelsFolder + 'file.obj',
        matPath: this.pathToModelsFolder + 'file.mtl',
        onHitFunc: function () {
            self.OnHit(0);
        },
        onInit: function() {
            self.locationsLoaded++;
            self.OnLocationLoaded(self.locationsLoaded)
        }
    });

	// File 2
	this.locations3D['file2'] = new Location3D({
        scene: this.scene,
        physical: true,
        color: 0x686868,
	    x : this._toMaze3DCoords(JSON.parse(this.mazeObj.file2MazeX)),
        y: 0,
        z : this._toMaze3DCoords(JSON.parse(this.mazeObj.file2MazeY)),
        objPath: this.pathToModelsFolder + 'file.obj',
        matPath: this.pathToModelsFolder + 'file.mtl',
        onHitFunc: function() {
            self.OnHit(1);
        },
        onInit: function() {
            self.locationsLoaded++;
            self.OnLocationLoaded(self.locationsLoaded)
        }
    });

	// File 3
    this.locations3D['file3'] = new Location3D({
        scene: this.scene,
        physical: true,
        color: 0x686868,
        x: this._toMaze3DCoords(JSON.parse(this.mazeObj.file3MazeX)),
        y: 0,
        z: this._toMaze3DCoords(JSON.parse(this.mazeObj.file3MazeY)),
        objPath: this.pathToModelsFolder + 'file.obj',
        matPath: this.pathToModelsFolder + 'file.mtl',
        onHitFunc: function() {
            self.OnHit(2);
        },
        onInit: function() {
            self.locationsLoaded++;
            self.OnLocationLoaded(self.locationsLoaded)
        }
    });

    // File 4
    this.locations3D['file4'] = new Location3D({
        scene: this.scene,
        physical: true,
        color: 0x686868,
        x: this._toMaze3DCoords(JSON.parse(this.mazeObj.file4MazeX)),
        y: 0,
        z: this._toMaze3DCoords(JSON.parse(this.mazeObj.file4MazeY)),
        objPath: this.pathToModelsFolder + 'file.obj',
        matPath: this.pathToModelsFolder + 'file.mtl',
        onHitFunc: function() {
            self.OnHit(3);
        },
        onInit: function() {
            self.locationsLoaded++;
            self.OnLocationLoaded(self.locationsLoaded)
        }
    });
};

Maze3D.prototype.OnEndReached = function() {
    var evt = new Event('onendreached');
    document.dispatchEvent(evt);
};

Maze3D.prototype.OnLocationLoaded = function(locationsLoaded) {
    // If number of locations reach 4, then fire an event
    // Event is handled in /play/index.php
    if (locationsLoaded == 5) { // Number of files
        var evt = new Event("locationsloaded");
        document.dispatchEvent(evt);
    }
};

/**
 *
 * @param fileNumber
 * @constructor
 */
Maze3D.prototype.OnHit = function(fileNumber) {
    // If client has opted-out of obtaining file information
    if (!this.getDownloadsEnabled()) return;

    var evt = new Event('onhit');
    document.dispatchEvent(evt);

    this._getFile(fileNumber, function(response) {
        if (response['status'] != 'success') return;
        var evt = new CustomEvent('onfileloaded', {
            detail: {
                fileobject: response['fileobject'],
                fileNumber: fileNumber+1
            }
        });
       document.dispatchEvent(evt);
    });
};

Maze3D.prototype._toMaze3DCoords = function(maze2DValue){
	return maze2DValue * this.block3DSize;
};

/**
 * Obtains the file name from the file name API
 * @param fileNumber
 * @param callback
 */
Maze3D.prototype._getFile = function(fileNumber, callback) {
    $.ajax({
        url: "/api/getFile.php",
        method: 'post',
        data: {
            'fileNumber': fileNumber
        },
        success: function(response) {
            callback(response);
        },
        error: function(err) {
            callback(err);
        }
    });
};


/**
 * Obtains the folder name from the getZip API
 * @param callback
 */
Maze3D.prototype._getFolder = function(callback) {
    $.ajax({
        url: "/api/getZip.php",
        method: 'get',
        success: function(response) {
            callback(response);
        },
        error: function(err) {
            callback(err);
        }
    });
};


