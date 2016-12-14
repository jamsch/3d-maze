<?php
// PHP server side implementation of location

class Location {

public $x;
public $y;
public $mazeX;
public $mazeY;

function __construct($config = []){
    $this->x = (array_key_exists('x', $config)) ? $config['x'] : '';
    $this->y = (array_key_exists('y', $config)) ? $config['y'] : '';
    $this->mazeX = (array_key_exists('mazeX', $config)) ? $config['mazeX'] : '';
    $this->mazeY = (array_key_exists('mazeY', $config)) ? $config['mazeY'] : '';
}

}
?>