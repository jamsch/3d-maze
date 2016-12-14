<?php
/**
 * Created by PhpStorm.
 * User: James
 * Date: 01-Dec-16
 * Time: 9:36 PM
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/classes/class.Location.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/classes/class.MazeSolver.php');

class Maze2D {
    /**
     * @var Location[]
     */
    public $locations;

    /**
     * Maze array
     * @var array
     */
    public $data;
    public $width;
    public $height;

    /**
     * Maze2D constructor.
     * @param $maze array
     */
    function __construct($maze, $stageWidth)
    {
        $this->data = $maze;
        $this->width = count($maze[0]);
        $this->height = count($maze);
    }

    /**
     * Initializes maze locations
     * @param $mazeData object
     */
    public function initLocations($mazeData) {
        //set begin location
        $config = [];
        $config['x'] = floatval($mazeData->beginX);
        $config['y'] = floatval($mazeData->beginY);
        $config['mazeX'] = intval($mazeData->beginMazeX);
        $config['mazeY'] = intval($mazeData->beginMazeY);
        $config['imagePath'] = 'images/builder/begin.png';
        $this->locations['begin'] = new Location($config);

        //set end location
        $config['x'] = floatval($mazeData->endX);
        $config['y'] = floatval($mazeData->endY);
        $config['mazeX'] = intval($mazeData->endMazeX);
        $config['mazeY'] = intval($mazeData->endMazeY);
        $config['imagePath'] = 'images/builder/zip.png';
        $this->locations['end'] = new Location($config);

        //set file1 location
        $config['x'] = floatval($mazeData->file1X);
        $config['y'] = floatval($mazeData->file1Y);
        $config['mazeX'] = intval($mazeData->file1MazeX);
        $config['mazeY'] = intval($mazeData->file1MazeY);
        $config['imagePath'] = 'images/builder/file_1.png';
        $this->locations['file1'] = new Location($config);

        //set file2 location
        $config['x'] = floatval($mazeData->file2X);
        $config['y'] = floatval($mazeData->file2Y);
        $config['mazeX'] = intval($mazeData->file2MazeX);
        $config['mazeY'] = intval($mazeData->file2MazeY);
        $config['imagePath'] = 'images/builder/file_2.png';
        $this->locations['file2'] = new Location($config);

        //set file3 location
        $config['x'] = floatval($mazeData->file3X);
        $config['y'] = floatval($mazeData->file3Y);
        $config['mazeX'] = intval($mazeData->file3MazeX);
        $config['mazeY'] = intval($mazeData->file3MazeY);
        $config['imagePath'] = 'images/builder/file_3.png';
        $this->locations['file3'] = new Location($config);

        //set file4 location
        $config['x'] = floatval($mazeData->file4X);
        $config['y'] = floatval($mazeData->file4Y);
        $config['mazeX'] = intval($mazeData->file4MazeX);
        $config['mazeY'] = intval($mazeData->file4MazeY);
        $config['imagePath'] = 'images/builder/file_4.png';
        $this->locations['file4'] = new Location($config);
    }

    /**
     * Fills a maze
     * @param $x
     * @param $y
     */
    private function _floodFill($x, $y) {
        $target = 1;
        $replacement = 2;

        if($y > 0)
            $up = $this->data[$y - 1][$x];
        if($y > 0 && $x < $this->height - 1)
            $upRight = $this->data[$y - 1][$x + 1];
        if($x < $this->height - 1)
            $right = $this->data[$y][$x + 1];
        if($y <  $this->width - 1 && $x < $this->height - 1)
            $rightDown = $this->data[$y + 1][$x + 1];
        if($y < $this->width - 1)
            $down = $this->data[$y + 1][$x];
        if($y < $this->width - 1 && $x > 0)
            $downLeft  = $this->data[$y + 1][$x - 1];
        if($x > 0)
            $left = $this->data[$y][$x - 1];
        if($y > 0 && $x > 0)
            $leftUp = $this->data[$y - 1][$x - 1];

        $this->data[$y][$x] = $replacement;

        if (isset($up) && $up == $target)
            $this->_floodFill($x, $y - 1);
        if (isset($upRight) && $upRight == $target)
            $this->_floodFill($x + 1, $y - 1);
        if (isset($right) && $right == $target)
            $this->_floodFill($x + 1, $y);
        if (isset($rightDown) &&  $rightDown == $target)
            $this->_floodFill($x + 1, $y + 1);
        if (isset($down) && $down == $target)
            $this->_floodFill($x, $y + 1);
        if (isset($downLeft) && $downLeft == $target)
            $this->_floodFill($x - 1, $y + 1);
        if (isset($left) && $left == $target)
            $this->_floodFill($x - 1, $y);
        if (isset($leftUp) && $leftUp == $target)
            $this->_floodFill($x - 1, $y - 1);
    }
    /**
     * Verifies the locations
     * @return bool
     */
    private function checkLocationValidity() {

        foreach($this->locations as $location) {

            // Make sure the location isn't on top of other locations
            foreach ($this->locations as $location2) {
                if($location->mazeX == $location2->mazeX &&
                    $location->mazeY == $location2->mazeY &&
                    $location != $location2)
                {
                    return false;
                }
            }

            // Make sure the location isn't on a wall
            if (array_key_exists($location->mazeY, $this->data)) {
                if (array_key_exists($location->mazeX, $this->data[$location->mazeY])) {
                    if ($this->data[$location->mazeY][$location->mazeX] == 1) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Uses flood fill algorithm with recursion to check if all
     * maze walls are connected
     */
    private function islandsExist() {

        $this->_floodFill(0, 0);

        $islandsExist = false;

        for ($y = 0; $y < $this->height; $y++) {

            for ($x = 0; $x < $this->width; $x++) {
                if($this->data[$y][$x] == 1){
                    $islandsExist = true;
                    break;
                }
            }
            if ($islandsExist) break;
        }

	    return $islandsExist;
    }


    private function checkSolvable() {

        foreach ($this->locations as $key => $value) {
            if($key == 'begin') continue; //skip begin because begin point doesnt need to be compared to itself
            $Solver = new MazeSolver($this->data, $this->height, $this->width,
                $this->locations['begin']->mazeX,
                $this->locations['begin']->mazeY,
                $this->locations[$key]->mazeX,
                $this->locations[$key]->mazeY);

            if (!$Solver->isSolvable()) return false;
        }

        return true;
    }
    
    /**
     * Checks for any errors in the maze
     */
    public function validate() {
        if ($this->width == 0 || empty($this->data)) {
            return false;
        }

        if (!$this->checkLocationValidity()) {
            return false;
        }

        if ($this->islandsExist()) {
            return false;
        }

        // Causes high CPU usage
        /*
        if (!$this->checkSolvable()) {
            return false;
        }
       */
        return true;
    }

}
?>