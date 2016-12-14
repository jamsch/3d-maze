<?php
/**
 * File: SimpleImage.php
 * Author: Simon Jarvis
 * Modified by: Miguel Fermín
 * Based in: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
 * 
 * This program is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License 
 * as published by the Free Software Foundation; either version 2 
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
 * GNU General Public License for more details: 
 * http://www.gnu.org/licenses/gpl.html
 *
 * Modified January 2, 2014 by Brannon Dorsey <brannon@brannondorsey.com>
 */

class SimpleImage {
   
	var $image;
	var $image_type;

	function __construct($filename = null) {
		if (!empty($filename)) {
			$this->load($filename);
		}
	}

	function load($filename, $useString=false)
	{
		
		if ($useString) {
			$this->image = imagecreatefromstring($filename);
		} else {
			$image_info = getimagesize($filename);
			$this->image_type = $image_info[2];
			if ($this->image_type == IMAGETYPE_JPEG) {
			$this->image = imagecreatefromjpeg($filename);
			} elseif ($this->image_type == IMAGETYPE_GIF) {
				$this->image = imagecreatefromgif($filename);
			} elseif ($this->image_type == IMAGETYPE_PNG) {
				$this->image = imagecreatefrompng($filename);
			} else {
				throw new Exception("The file you're trying to open is not supported");
			}
		}
	}

	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null)
	{
		if ($image_type == IMAGETYPE_JPEG) {
			imagejpeg($this->image,$filename,$compression);
		} elseif ($image_type == IMAGETYPE_GIF) {
			imagegif($this->image,$filename);         
		} elseif ($image_type == IMAGETYPE_PNG) {
			imagepng($this->image,$filename, $compression);
		}

		if ($permissions != null) {
			chmod($filename,$permissions);
		}
	}

	function output($image_type=IMAGETYPE_JPEG, $quality = 80)
	{
		if ($image_type == IMAGETYPE_JPEG) {
			header("Content-type: image/jpeg");
			imagejpeg($this->image, null, $quality);
		} elseif ($image_type == IMAGETYPE_GIF) {
			header("Content-type: image/gif");
			imagegif($this->image);         
		} elseif ($image_type == IMAGETYPE_PNG) {
			header("Content-type: image/png");
			imagepng($this->image);
		}
	}

	function getWidth()
	{
		return imagesx($this->image);
	}

	function getHeight()
	{
		return imagesy($this->image);
	}

	function resizeToHeight($height)
	{
		$ratio = $height / $this->getHeight();
		$width = round($this->getWidth() * $ratio);
		$this->resize($width,$height);
	}

	function resizeToWidth($width)
	{
		$ratio = $width / $this->getWidth();
		$height = round($this->getHeight() * $ratio);
		$this->resize($width,$height);
	}

	function square($size)
	{
		$new_image = imagecreatetruecolor($size, $size);

		if ($this->getWidth() > $this->getHeight()) {
			$this->resizeToHeight($size);
			
			imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
			imagealphablending($new_image, false);
			imagesavealpha($new_image, true);
			imagecopy($new_image, $this->image, 0, 0, ($this->getWidth() - $size) / 2, 0, $size, $size);

		} else {
			$this->resizeToWidth($size);
			
			imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
			imagealphablending($new_image, false);
			imagesavealpha($new_image, true);
			imagecopy($new_image, $this->image, 0, 0, 0, ($this->getHeight() - $size) / 2, $size, $size);

		}

		$this->image = $new_image;
	}
   
	function scale($scale)
	{
		$width = $this->getWidth() * $scale/100;
		$height = $this->getHeight() * $scale/100; 
		$this->resize($width,$height);
	}
   
	function resize($width,$height)
	{
		$new_image = imagecreatetruecolor($width, $height);
		
		imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
		imagealphablending($new_image, false);
		imagesavealpha($new_image, false);
		
		imagecopyresampled($new_image, $this->image, 0, 0, 1, 1, $width, $height, $this->getWidth() - 1, $this->getHeight() - 1);
		$this->image = $new_image;   
	}

    function cut($x, $y, $width, $height)
    {
    	$new_image = imagecreatetruecolor($width, $height);	

		imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
		imagealphablending($new_image, false);
		imagesavealpha($new_image, true);

		imagecopy($new_image, $this->image, 0, 0, $x, $y, $width, $height);

		$this->image = $new_image;
	}

	function maxarea($width, $height = null)
	{
		$height = $height ? $height : $width;
		
		if ($this->getWidth() > $width) {
			$this->resizeToWidth($width);
		}
		if ($this->getHeight() > $height) {
			$this->resizeToheight($height);
		}
	}

	function cutFromCenter($width, $height)
	{
		
		if ($width < $this->getWidth() && $width > $height) {
			$this->resizeToWidth($width);
		}
		if ($height < $this->getHeight() && $width < $height) {
			$this->resizeToHeight($height);
		}
		
		$x = ($this->getWidth() / 2) - ($width / 2);
		$y = ($this->getHeight() / 2) - ($height / 2);
		
		return $this->cut($x, $y, $width, $height);
	}

	function maxareafill($width, $height, $red = 0, $green = 0, $blue = 0)
	{
	    $this->maxarea($width, $height);
	    $new_image = imagecreatetruecolor($width, $height); 
	    $color_fill = imagecolorallocate($new_image, $red, $green, $blue);
	    imagefill($new_image, 0, 0, $color_fill);        
	    imagecopyresampled($new_image,
	    					$this->image, 
	    					floor(($width - $this->getWidth())/2), 
	    					floor(($height-$this->getHeight())/2), 
	    					0, 0, 
	    					$this->getWidth(), 
	    					$this->getHeight(), 
	    					$this->getWidth(), 
	    					$this->getHeight()
	    				); 
	    $this->image = $new_image;
	}

    /**
     * Optimizes PNG file with pngquant 1.8 or later (reduces file size of 24-bit/32-bit PNG images).
     *
     * You need to install pngquant 1.8 on the server (ancient version 1.0 won't work).
     * There's package for Debian/Ubuntu and RPM for other distributions on http://pngquant.org
     *
     * @param $path_to_png_file string - path to any PNG file, e.g. $_FILE['file']['tmp_name']
     * @param $max_quality int - conversion quality, useful values from 60 to 100 (smaller number = smaller file)
     * @param $output_path string - Output location
     * @throws Exception - happens when there is a problem converting
     * @return string - content of PNG file after conversion
     */
    function compress_png($path_to_png_file, $max_quality = 90)
    {
        if (!file_exists($path_to_png_file)) {
            throw new Exception("File does not exist: $path_to_png_file");
        }

        // guarantee that quality won't be worse than that.
        $min_quality = 60;

        // '-' makes it use stdout, required to save to $compressed_png_content variable
        // '<' makes it read from the given file path
        // escapeshellarg() makes this safe to use with any path
        $shell_query = "pngquant --quality=$min_quality-$max_quality ".escapeshellarg($path_to_png_file)." --output ".escapeshellarg($path_to_png_file) . " --force";
        $compressed_png_content = shell_exec($shell_query);
        /*
        if (!$compressed_png_content) {
            throw new Exception("Conversion to compressed PNG failed. Is pngquant 1.8+ installed on the server?");
        }*/

        return $compressed_png_content;
    }


}