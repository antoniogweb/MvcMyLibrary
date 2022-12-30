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
	
	public static $maxNumberOfFilesCached = 0; // if 0, there is no limit
	
	private $dinamicFiles = array();
	private $internalLoadChache = true; // intenal flag set by the checkNumberOfFiles method
	
	public $baseUrl = null; //the base url of the website: http://domainname
	public $baseUrlSrc = null; //the base url of the website (http://domainname) without the language
	
	public $absolutePath = null; // absolute path (se $folder below)
	public $folder = null; // folder where the cache files are saved, the path is relative path to the $absolutePath path (see above), 
	public $cacheKey = null; // the key of the cache, it should hold $_SERVER["REQUEST_URI"]
	public $saveHtml = false; // if the page has to be cached
	public $loadHtml = false; // if the cache has to be loaded
	public $fileWithCommands = null; // file with commands to be executed before load the cache
	
	private function __construct($absolutePath, $folder)
	{
		$this->absolutePath = $absolutePath;
		$this->folder = $folder;
		
		$this->baseUrl = rtrim(Url::getRoot(),"/");
		$this->baseUrlSrc = rtrim(Url::getFileRoot(),"/");
		
		// Check the number of files and set internalLoadChache
		$this->checkNumberOfFiles();
	}
	
	public static function getInstance($absolutePath = null, $folder = null)
	{
		if (!isset(self::$instance)) {
			$className = __CLASS__;
			self::$instance = new $className($absolutePath, $folder);
		}
		
		return self::$instance;
	}
	
	public function getInternalLoadChache()
	{
		return $this->internalLoadChache;
	}
	
	public function saved()
	{
		if ($this->internalLoadChache && $this->loadHtml && $this->cacheKey && is_file($this->getFullPath()."/".md5($this->cacheKey).".php"))
			return true;
		
		return false;
	}
	
	public function load($data)
	{
		if ($this->internalLoadChache && $this->loadHtml && $this->cacheKey && is_file($this->getFullPath()."/".md5($this->cacheKey).".php"))
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
		if ($this->internalLoadChache && !$cachable && $this->saveHtml && $this->folder)
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
	
	public function checkNumberOfFiles()
	{
		if (self::$maxNumberOfFilesCached && $this->folder)
		{
			$absolutePath = $this->checkCacheFolder();
			
			$iterator = new FilesystemIterator($absolutePath, FilesystemIterator::SKIP_DOTS);
			$numberOfFilesCached = iterator_count($iterator);
			
			if ($numberOfFilesCached > self::$maxNumberOfFilesCached)
				$this->internalLoadChache = false;
		}
	}
	
	public function save($html)
	{
		if (!$this->cacheKey && isset($_SERVER["REQUEST_URI"]))
			$this->cacheKey = $_SERVER["REQUEST_URI"];
		
		if ($this->internalLoadChache && $this->folder && $this->cacheKey)
		{
			$absolutePath = $this->checkCacheFolder();
			
			$fileName = md5($this->cacheKey).".php";
			
			file_put_contents($absolutePath."/".$fileName, $html);
			
// 			file_put_contents($absolutePath."/log_files.log", json_encode($this->dinamicFiles));
			
			return $absolutePath."/".$fileName;
		}
		
		return null;
	}
}
