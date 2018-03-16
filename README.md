# 3DMaze

A 3D, collaborative maze game that consists of three parts: __Make__, __Draw__ and __Play__.

__Make__ allows users to create mazes. __Draw__ allows users to draw on the maze walls, and __Play__ allows users to enter the 3D maze and view the drawings.

## Prerequisites

- MySQL/MariaDB installed
- PHP 5 or 7 installed
- Modification of max file size upload (see: `upload_max_filesize`, `post_max_size` in PHP.ini)
- Web server software (nginx, lighthttpd, apache) installed
- Write access to files and folders inside the website directory for the __www-data__ user
- Optional: pngquant installed (a lossy PNG compressor for compressing of maze textures)

## Installation

1. Run db.sql to create the database and table structure for the maze 
2. Generate 256x256 png maze tiles in `images/maze/textures` between 0001.png to 0999.png using the python script `create_textures.py`
4. Place a reCAPTCHA secret key under `includes/secret/recaptcha-secret-key.php`


## Views

- `index.php` - The splash page that provides links to the `make`, `draw`, and `play` pages.
- `make/index.php` - Allows the user to change the structure of the maze, as well as upload files that are downloaded when players recover in-game items on the `play` page.
- `draw/index.php` - Allows the user to draw on the walls of the 3D maze on the `play` page.
- `play/index.php` - The 3D maze.

## APIs

- `api.php` - Provides a web accessible `JSON` representation of the 2D maze data in a way that can be translated by the `play` page into the 3D maze.
- `/api/upload.php` - Handles the backend saving of the files that represent in-game items and Finders Folder contents to the server's `uploads/` directory. Requests made to this page come from `play.php` when a player finds the Finder's Folder and is prompted to upload a file.
- `/api/saveimage.php` - Handles the saving of `HTML5` canvas images from `draw.php` to be saved in the `images/maze/textures`. This directory is the location where maze wall textures are loaded from in `play.php`.
- `/api/getFile.php` - Gets the file name of a specific file number in the maze
- `/api/getZip.php` - Gets the file name of the final folder in the Finder's folder 
- `/api/download_zip.php` - Requests to download the final zip folder
- `/api/download_file.php` - Requests to download a file
- `/api/update_maze.php` - Updates the maze with the user-sent data. Verifies if the maze has no errors such as unreachable endpoints or islands.

## Directories

- `!Important/` - contains database scripts and other files needed to run the project.
- `api/` - contains all the API logic for the project.
- `images/` - contains all images for the project.
- `models/` - contains the `.obj` and `.mtl` files for the 3D models used in `play.php`
- `uploads/` - contains the files uploaded by users in `make.php` as well as the `findersfolder.zip` and all Finders Folder files in a subdirectory.
- `styles/` - All `CSS` files used to style the site.
- `scripts/` - All `JavaScript` files that make the whole maze project work. There are a number of sub and subsub and subsubsubdirectories.
- `includes/` - All `PHP` includes. Contains both logic and content snippets.


## Misc
- `!Important/db.sql` - A `MySQL` file that contains the structure of the maze database.
- `includes/secret/recaptcha-secret-key.php` - The website private key for Google's reCAPTCHA.
- `images/maze/create_textures.py` - A python script for creating maze textures.
- `todo.md` - An ongoing todo list for the maze project.
