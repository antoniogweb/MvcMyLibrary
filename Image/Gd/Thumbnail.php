<?php

// MvcMyLibrary is a PHP framework for creating and managing dynamic content
//
// Copyright (C) 2009 - 2025  Antonio Gallo (info@laboratoriolibero.com)
// See COPYRIGHT.txt and LICENSE.txt.
//
// This file is part of MvcMyLibrary
//
// MvcMyLibrary is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// MvcMyLibrary is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with MvcMyLibrary.  If not, see <http://www.gnu.org/licenses/>.

if (!defined('EG')) die('Direct access not allowed!');

//class to create a thumbnail
class Image_Gd_Thumbnail
{
	const DS = DIRECTORY_SEPARATOR;
	
	private $params = array(); //parameters of the object
	private $basePath = null; //the path of the folder inside which the images are saved
	
	public static $cacheFolderFilesPermission = 0777;
	
	public $textOverlay = array();
	public $watermarks = array();
	
	public $imageRotations = array();
	
	public static $defaultJpegImgQuality = 75; // the default JPEG quality
	
	public static $memoryLimit = null; // if not null, the memory limit set with ini_set in the render method
	
	public function __construct($basePath,$params = null)
	{
		$finalChar = $basePath[strlen($basePath) - 1];
		if (strcmp($finalChar,self::DS) !== 0) $basePath .= self::DS;
		
		$this->basePath = $basePath;
		
		$defaultParams = array(
			'imgWidth'		=>	null,
			'imgHeight'		=>	null,
			'defaultImage'	=>	null,
			'cropImage'		=>	'no',
			'horizAlign'	=>	'left',
			'vertAlign'		=>	'top',
			'resample'		=>	'yes',
			'function'		=>	'none',
			'outputFormat'	=>	'jpeg',
			'backgroundColor' => null, //must be hex color
			'useCache'		=>	false,
			'forceToFormat'	=>	null, // if null, keet the same output format (it can be jpeg or png)
			'jpegImgQuality'	=>	self::$defaultJpegImgQuality, // the default JPEG quality
			'imageCreateFunctionJpeg'	=>	'imagecreatetruecolor',
			'imageCreateFunctionPng'	=>	'imagecreatetruecolor',
			'imageCreateFunctionGif'	=>	'imagecreatetruecolor',
			'imageTrueColorToPaletteJpeg'	=>	null,
			'imageTrueColorToPalettePng'	=>	null,
			'imageTrueColorToPaletteGif'	=>	null,
		);

		//set the $this->params array
		if (is_array($params))
		{
			foreach ($params as $key => $value)
			{
				$defaultParams[$key] = $value;
			}
		}
		
		$this->params = $defaultParams;
	}
	
	public function getSourceCoordinates($direction, $oldDim, $dim)
	{
		if ($direction === "x")
		{
			switch ($this->params['horizAlign'])
			{
				case 'left':
					$coordSrc = 0;
					break;
				case 'right':
					$coordSrc = floor(($oldDim-$dim));
					break;
				case 'center':
					$coordSrc = floor(($oldDim-$dim)/2);
					break;
				default:
					$coordSrc = $this->params['horizAlign'];
			}
		}
		else
		{
			switch ($this->params['vertAlign'])
			{
				case 'top':
					$coordSrc = 0;
					break;
				case 'bottom':
					$coordSrc = floor(($oldDim-$dim));
					break;
				case 'center':
					$coordSrc = floor(($oldDim-$dim)/2);
					break;
				default:
					$coordSrc = $this->params['vertAlign'];
			}
		}
		
		return $coordSrc;
	}
	
	//create the thumbnail
	//$imageName: the name of the file inside $this->basePath
	//$outputFile: the name of the output file
	public function render($imageFile, $outputFile = null, $cachePathForce = null)
	{
		if (isset(self::$memoryLimit))
			ini_set("memory_limit", self::$memoryLimit);
		
		//set if it has to create cache or not
		$createCache = false;
		
		$imageFile = basename((string)$imageFile);
		$imagePath = $this->basePath . $imageFile;
		
		$img = null;
		$type = 'jpeg';
		$contentType = 'image/jpeg';
		
		$u = new Files_Upload($this->basePath);
		$isValidPath = $u->isMatching($imagePath);
		
// 		if ((!file_exists($imagePath) or !$isValidPath) and isset($this->params['defaultImage'])) $imagePath = $this->params['defaultImage'];
		
		if (!file_exists($imagePath) or !$isValidPath)
		{
			return false;
			
// 			if (isset($this->params['defaultImage']))
// 				$imagePath = $this->params['defaultImage'];
// 			
// 			$outputFile = $cachePathForce = null;
		}
		
		//cache of the thumb
		if ($this->params['useCache'] && file_exists($imagePath) && $isValidPath)
		{
			//name of cache folder
			$cacheFolder = "cache_".$this->params['imgWidth']."_".$this->params['imgHeight'];
			//full path of cache folder
			$cachePath = $this->basePath.$cacheFolder;
			//full path of cached file
			$outputFileCache = $cachePath . "/$imageFile";
			
			if (!$u->isValidFolder($cachePath))
			{
				$u->createFolder($cacheFolder);
				
				if ($u->isMatching($outputFileCache))
				{
					$createCache = true;
				}
			}
			else
			{
				if ($u->isMatching($outputFileCache))
				{
					if (file_exists($outputFileCache))
					{
						$ext = $u->getFileExtension($imageFile);
						
						if (strcmp($ext,'jpg') === 0 or strcmp($ext,'jpeg') === 0) {
							$contentType = 'image/jpeg';
						} else if (strcmp($ext,'png') === 0) {
							$contentType = 'image/png';
						} else if (strcmp($ext,'gif') === 0) {
							$contentType = 'image/gif';
						}
						header('Content-Type:'.$contentType);
						header('Content-Length: ' . filesize($outputFileCache));
						readfile($outputFileCache);
						return;
					}
					else
					{
						$createCache = true;
					}
				}
			}
		}
		
		if (file_exists($imagePath) and $isValidPath)
		{
			$extArray = explode('.', $imagePath);
			$ext = strtolower(end($extArray));

			if (strcmp($ext,'jpg') === 0 or strcmp($ext,'jpeg') === 0) {
				$img = @imagecreatefromjpeg($imagePath);
				$type = 'jpeg';
				$contentType = 'image/jpeg';
			} else if (strcmp($ext,'png') === 0) {
				$img = @imagecreatefrompng($imagePath);
				$type = 'png';
				$contentType = 'image/png';
			} else if (strcmp($ext,'gif') === 0) {
				$img = @imagecreatefromgif($imagePath);
				$type = 'gif';
				$contentType = 'image/gif';
			}
		}
		
		//If an image was successfully loaded, test the image for size
		if ($img)
		{
			//image size
			$width = imagesx($img);
			$height = imagesy($img);

			if (!isset($this->params['imgWidth']))	$this->params['imgWidth'] = $width;
			if (!isset($this->params['imgHeight']))	$this->params['imgHeight'] = $height;
			
			if ($this->params['cropImage'] === 'no')
			{
				$scale = min($this->params['imgWidth']/$width, $this->params['imgHeight']/$height);
			}
			else if ($this->params['cropImage'] === 'yes')
			{
				$scale = max($this->params['imgWidth']/$width, $this->params['imgHeight']/$height);
			}

			$xSrc = 0; //x coordinate of source image
			$ySrc = 0; //y coordinate of source image
			
			$xDst = 0; //x coordinate of destination image
			$yDst = 0; //y coordinate of destination image
			
			if ($this->params['cropImage'] === 'no')
			{
				if ($scale <= 1)
				{
					$newWidth = $backWidth = floor($scale*$width);
					$newHeight = $backHeight = floor($scale*$height);
					
					if ($this->params['backgroundColor'])
					{
						$backWidth = $this->params['imgWidth'];
						$backHeight = $this->params['imgHeight'];
						
						if ($backWidth > $newWidth)
						{
							$xDst = floor(($backWidth-$newWidth)/2);
						}
						else if ($backHeight > $newHeight)
						{
							$yDst = floor(($backHeight-$newHeight)/2);
						}
					}
				}
				else
				{
					$newWidth = $backWidth = $width;
					$newHeight = $backHeight = $height;
					
					if ($this->params['backgroundColor'])
					{
						$backWidth = $this->params['imgWidth'];
						$backHeight = $this->params['imgHeight'];
						
						$xDst = floor(($backWidth-$newWidth)/2);
						$yDst = floor(($backHeight-$newHeight)/2);
					}
				}
			}
			else if ($this->params['cropImage'] === 'yes')
			{
				if ($scale < 1)
				{
					$newWidth = $backWidth = $this->params['imgWidth'];
					$newHeight = $backHeight = $this->params['imgHeight'];
					$oldWidth = $width;
					$oldHeight = $height;
					$width = floor($newWidth/$scale);
					$height = floor($newHeight/$scale);
					
					$xSrc = $this->getSourceCoordinates("x",$oldWidth,$width);
					$ySrc = $this->getSourceCoordinates("y",$oldHeight,$height);
					
				}
				else
				{
					$oldWidth = $width;
					$oldHeight = $height;
					
					if ($width <= $this->params['imgWidth'] and $height <= $this->params['imgHeight'])
					{
						$newWidth = $backWidth = $width;
						$newHeight = $backHeight = $height;
						
						if ($this->params['backgroundColor'])
						{
							$backWidth = $this->params['imgWidth'];
							$backHeight = $this->params['imgHeight'];
							
							$xDst = floor(($backWidth-$newWidth)/2);
							$yDst = floor(($backHeight-$newHeight)/2);
						}
					
					}
					else if ($width <= $this->params['imgWidth'])
					{
						$newWidth = $backWidth = $width;
						$newHeight = $backHeight = $height = $this->params['imgHeight'];
						
						$ySrc = $this->getSourceCoordinates("y",$oldHeight,$height);
						
						if ($this->params['backgroundColor'])
						{
							$backWidth = $this->params['imgWidth'];
							$backHeight = $this->params['imgHeight'];
							
							$xDst = floor(($backWidth-$newWidth)/2);
						}
					
					}
					else if ($height <= $this->params['imgHeight'])
					{
						$newHeight = $backHeight = $height;
						$newWidth = $backWidth = $width = $this->params['imgWidth'];
						
						$xSrc = $this->getSourceCoordinates("x",$oldWidth,$width);
						
						if ($this->params['backgroundColor'])
						{
							$backWidth = $this->params['imgWidth'];
							$backHeight = $this->params['imgHeight'];
							
							$yDst = floor(($backHeight-$newHeight)/2);
						}
						
					}
				}
			}
			
			//temp image
			if ($this->params["imageCreateFunction".ucfirst($type)] == "imagecreatetruecolor")
				$tmpImg = imagecreatetruecolor($backWidth, $backHeight);
			else if ($this->params["imageCreateFunction".ucfirst($type)] == "imagecreate")
				$tmpImg = imagecreate($backWidth, $backHeight);
			else
				$tmpImg = imagecreatetruecolor($backWidth, $backHeight);
			
			//set background color if backgroundColor param is not null (hex value)
			if ($this->params['backgroundColor'])
			{
				if (strcmp($this->params['backgroundColor'],"transparent") !== 0)
				{
					$rgbColor = hex2rgb($this->params['backgroundColor']);
					
					$backgroundC = imagecolorallocate($tmpImg,$rgbColor[0],$rgbColor[1],$rgbColor[2]);
				}
				else
				{
					$backgroundC = imagecolortransparent($tmpImg);
				}
				
				imagefill($tmpImg, 0, 0, $backgroundC);
			}
			
			if(strcmp($type,'png') === 0 or strcmp($type,'gif') === 0){
				
				if ($this->params['backgroundColor'])
				{
					imagealphablending($tmpImg, true);
				}
				else
				{
					imagealphablending($tmpImg, false);
				}
				
				imagesavealpha($tmpImg, true);
			}

			if ($this->params['resample'] === 'yes')
			{
				//copy and resample
				imagecopyresampled($tmpImg, $img, $xDst, $yDst, $xSrc, $ySrc,$newWidth, $newHeight, $width, $height);
			}
			else
			{
				//copy and resize
				imagecopyresized($tmpImg, $img, $xDst, $yDst, $xSrc, $ySrc,$newWidth, $newHeight, $width, $height);
			}
			imagedestroy($img);
			
			if ($this->params["imageTrueColorToPalette".ucfirst($type)])
				imagetruecolortopalette($tmpImg, false, $this->params["imageTrueColorToPalette".ucfirst($type)]);
			
			$img = $tmpImg;

			if (!function_exists($this->params['function']))
			{
				throw new Exception('Error in <b>'.__METHOD__.'</b>: function <b>'.$this->params['function']. '</b> does not exist');
			}

			$img = call_user_func($this->params['function'],$img);
		}
		
		if (!$img)
		{
			$createCache = false;
			
			$imgWidth = isset($this->params['imgWidth']) ? $this->params['imgWidth'] : 100;
			$imgHeight = isset($this->params['imgHeight']) ? $this->params['imgHeight'] : 100;
			
			$img = imagecreate($imgWidth, $imgHeight);
			imagecolorallocate($img,200,200,200);
			
		}

		foreach ($this->textOverlay as $text)
		{
			if (isset($text["font"]) && isset($text["text"]) && $text["text"])
			{
				if (isset($text["color"]) && is_array($text["color"]) && count($text["color"]) === 3)
					$color = imagecolorallocate($img, $text["color"][0], $text["color"][1], $text["color"][2]);
				else
					$color = imagecolorallocate($img, 255, 255, 255);
				
				$x = $text["x"];
				
				if ($text["x"] == "center")
				{
					if (isset($this->params['imgWidth']))
						$centerX = $this->params['imgWidth'] / 2;
					else
						$centerX = $width / 2;
					
					list($left, $bottom, $right, , , $top) = imageftbbox($text["size"], $text["angle"], $text["font"], $text["text"]);
					
					$left_offset = ($right - $left) / 2;
					$x = $centerX - $left_offset;
					
					if (isset($text["x-offset"]))
						$x += $text["x-offset"];
				}
				
				if (isset($text["align"]))
				{
					switch ($text["align"])
					{
						case "center";
							$textWidth = imagettfbbox($text["size"], $text["angle"], $text["font"], $text["text"]);
							
							$x -= (int)($textWidth[2] / 2);
							break;
					}
				}
				
				imagettftext($img, $text["size"], $text["angle"], $x, $text["y"], $color, $text["font"], $text["text"]);
			}
		}
		
		foreach ($this->watermarks as $wm)
		{
			if (isset($wm["imagePath"]) && isset($wm["x"]) && $wm["y"])
			{
				if (file_exists($wm["imagePath"]))
				{
					$wmImage = imagecreatefromstring(file_get_contents($wm["imagePath"]));
					
					list($wmWidth, $wmHeight) = getimagesize($wm["imagePath"]);
					
					imagecopy($img, $wmImage, $wm["x"], $wm["y"], 0, 0, $wmWidth, $wmHeight);
				}
			}
		}
		
		foreach ($this->imageRotations as $rotation)
		{
			if (isset($rotation["angle"]))
			{
				$img = imagerotate($img, $rotation["angle"], 0);
			}
		}
		
		if ($this->params['forceToFormat'] && in_array($this->params['forceToFormat'], array("jpeg","png")))
		{
			$type = $this->params['forceToFormat'];
			$contentType = 'image/'.$this->params['forceToFormat'];
		}
		
		//print the image
		if (!isset($outputFile))
		{
			header("Content-type: $contentType");
		}
		
		// Folder creation
		if ($cachePathForce && !file_exists(ROOT."/".$cachePathForce))
			@mkdir(ROOT."/".$cachePathForce,self::$cacheFolderFilesPermission,true);
		
		if (strcmp($type,'png') === 0)
		{
			imagepng($img,$outputFile,9);
			
			if ($createCache)
			{
				imagepng($img,$outputFileCache,9);
			}
			
			if ($cachePathForce)
				imagepng($img,ROOT."/".$cachePathForce."/".$imageFile, 9);
		}
		else if (strcmp($type,'gif') === 0)
		{
			imagegif($img,$outputFile);
			
			if ($createCache)
			{
				imagegif($img,$outputFileCache);
			}
			
			if ($cachePathForce)
				imagegif($img,ROOT."/".$cachePathForce."/".$imageFile);
		}
		else
		{
			imagejpeg($img,$outputFile, $this->params['jpegImgQuality']);
			
			if ($createCache)
			{
				imagejpeg($img,$outputFileCache, $this->params['jpegImgQuality']);
			}
			
			if ($cachePathForce)
				imagejpeg($img,ROOT."/".$cachePathForce."/".$imageFile, $this->params['jpegImgQuality']);
		}
		
	}
	
}
