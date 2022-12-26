<?php

// MvcMyLibrary is a PHP framework for creating and managing dynamic content
//
// Copyright (C) 2009 - 2022  Antonio Gallo (info@laboratoriolibero.com)
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

class Cache_Html
{
	private static $instance = null; //instance of this class
	private static $folderChecked = false;
	private static $folderDynamicChecked = false;
	
	private $dinamicFiles = array();
	
	public $baseUrl = null; //the base url of the website: http://domainname
	public $baseUrlSrc = null; //the base url of the website (http://domainname) without the language
	
	public $absolutePath = null;
	public $folder = null;
	public $cacheKey = null;
	public $saveHtml = false;
	public $loadHtml = false;
	public $fileWithCommands = null;
	
	private function __construct()
	{
		$this->baseUrl = rtrim(Url::getRoot(),"/");
		$this->baseUrlSrc = rtrim(Url::getFileRoot(),"/");
	}

	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			$className = __CLASS__;
			self::$instance = new $className();
		}
		
		return self::$instance;
	}
	
	public function saved()
	{
		if ($this->loadHtml && $this->cacheKey && is_file($this->getFullPath()."/".md5($this->cacheKey).".php"))
			return true;
		
		return false;
	}
	
	public function load($data)
	{
		if ($this->loadHtml && $this->cacheKey && is_file($this->getFullPath()."/".md5($this->cacheKey).".php"))
		{
			extract($data);
			
			include($this->getFullPath()."/".md5($this->cacheKey).".php");
		}
	}
	
	public function getFullPath()
	{
		return rtrim($this->absolutePath,"/")."/".rtrim($this->folder,"/");
	}
	
	public function saveDynamic($path, $cachable = true, $stringaCache = '')
	{
		if (!$cachable && $this->saveHtml)
		{
			$fileName = md5($stringaCache.$path).".php";
			
			$this->dinamicFiles[] = $stringaCache.$path;
			
			$absolutePath = $this->checkCacheFolder();
			
			if ($this->folder)
			{
				$html = "<?php echo '<?php $stringaCache include(\'".$path."\'); ?>' ?>";
				
				file_put_contents($absolutePath."/DynamicFiles/".$fileName, $html);
				
				return $absolutePath."/DynamicFiles/".$fileName;
			}
			else
				return $path;
		}
		else
			return $path;
	}
	
	public function checkCacheFolder()
	{
		$absolutePath = $this->getFullPath();
		
		if (!self::$folderChecked && !is_dir($absolutePath) && $this->folder)
		{
			createFolderFull($this->folder, $this->absolutePath);
			
			self::$folderChecked = true;
		}
		
		if (!self::$folderDynamicChecked && !is_dir($absolutePath."/DynamicFiles") && $this->folder)
		{
			createFolderFull($this->folder."/DynamicFiles", $this->absolutePath);
			
			self::$folderDynamicChecked = true;
		}
		
		return $absolutePath;
	}
	
	public function save($html)
	{
		if (!$this->cacheKey && isset($_SERVER["REQUEST_URI"]))
			$this->cacheKey = $_SERVER["REQUEST_URI"];
		
		$fileName = md5($this->cacheKey).".php";
		
		$absolutePath = $this->checkCacheFolder();
		
		if ($this->folder)
		{
			file_put_contents($absolutePath."/".$fileName, $html);
			
			file_put_contents($absolutePath."/log_files.log", json_encode($this->dinamicFiles));
			
			return $absolutePath."/".$fileName;
		}
		
		return null;
	}
}
