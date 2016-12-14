<?php
/**
 * Created by PhpStorm.
 * User: James
 * Date: 02-Dec-16
 * Time: 9:48 AM
 */

class MazeSolver {

    /**
     * Maze array
     * @var array
     */
    private $maze;
    private $mazeWidth;
    private $mazeHeight;
    private $player;
    /**
     * @var stdClass
     */
    private $startingPoint;
    /**
     * @var stdClass
     */
    private $endingPoint;
    private $wall;
    private $free;
    private $up;
    private $right;
    private $down;
    private $left;

    /**
     * MazeSolver constructor.
     * @param $data array
     * @param $height integer
     * @param $width integer
     * @param $beginMazeX integer
     * @param $beginMazeY integer
     * @param $endMazeX integer
     * @param $endMazeY integer
     */
    function __construct($data, $height, $width, $beginMazeX, $beginMazeY, $endMazeX, $endMazeY)
    {
        $this->maze = $data; //data.slice(0); //make a copy not reference
        $this->mazeHeight = $height; //data.slice(0); //make a copy not reference
        $this->mazeWidth = $width; //data.slice(0); //make a copy not reference

        $this->startingPoint = new stdClass();
        $this->startingPoint->x = $beginMazeX;
        $this->startingPoint->y = $beginMazeY;

        $this->endingPoint = new stdClass();
        $this->endingPoint->x = $endMazeX;
        $this->endingPoint->y = $endMazeY;

        $this->wall = 1;
        $this->free = 0;
        $this->player = 2;

        $this->up = 1;
        $this->right = 2;
        $this->down = 3;
        $this->left = 4;
    }

    public function isSolvable() {
        return $this->solve($this->startingPoint->x, $this->startingPoint->y);
    }

    private function solve($x, $y)
    {
        // Make the move (if it's wrong, we will backtrack later.
        $this->maze[$y][$x] = $this->player;
        // If you want progressive update, uncomment these lines...

        // Check if we have reached our goal.
        if ($x == $this->endingPoint->x && $y == $this->endingPoint->y)
        {
            return true;
        }

        // Recursively search for our goal.
        if ($x > 0 && $this->maze[$y][$x-1] == $this->free && $this->solve($x - 1, $y))
        {
            return true;
        }
        if ($x < $this->mazeWidth && $this->maze[$y][$x + 1] == $this->free && $this->solve($x + 1, $y))
        {
            return true;
        }
        if ($y > 0 && $this->maze[$y-1][$x] == $this->free && $this->solve($x, $y - 1))
        {
            return true;
        }
        if ($y < $this->mazeHeight && $this->maze[$y + 1][$x] == $this->free && $this->solve($x, $y + 1))
        {
            return true;
        }

        // Otherwise we need to backtrack and find another solution.
        $this->maze[$y][$x] = $this->free;

        return false;
    }
}