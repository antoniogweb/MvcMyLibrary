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
	private static $folderIncludeChecked = false;
	
	public static $maxNumberOfFilesCached = 0; // if 0, there is no limit
	
	protected $_data = array();
	
	private $dinamicFiles = array();
	private $internalLoadChache = true; // intenal flag set by the checkNumberOfFiles method
	
	public $baseUrl = null; //the base url of the website: http://domainname
	public $baseUrlSrc = null; //the base url of the website (http://domainname) without the language
	
	public $controller = 'controller';
	public $application = null;
	public $applicationUrl = null; //the url of the application
	public $action = '';
	public $currPage; //the URL of the current page
	
	public $absolutePath = null; // absolute path (se $folder below)
	public $folder = null; // folder where the cache files are saved, the path is relative path to the $absolutePath path (see above), 
	public $partialKey = ""; // get the key used for files to be included
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
	
	public function setData($data)
	{
		$this->_data = $data;
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
	
	public function load()
	{
		if ($this->internalLoadChache && $this->loadHtml && $this->cacheKey && is_file($this->getFullPath()."/".md5($this->cacheKey).".php"))
		{
			extract($this->_data);
			
			include($this->getFullPath()."/".md5($this->cacheKey).".php");
		}
	}
	
	public function getFullPath()
	{
		return rtrim($this->absolutePath,"/")."/".rtrim($this->folder,"/");
	}
	
	public function saveDynamic($path, $cachable = true, $stringaCache = '', $cachedTemplateFile = false)
	{
		if ($this->internalLoadChache && $this->saveHtml && $this->folder && (!$cachable || $cachedTemplateFile))
		{
			$absolutePath = $this->checkCacheFolder();
			
			if ($this->folder)
			{
				$fileNameRandom = randomToken(20).".php";
				
				if (!$cachable)
				{
					$html = "<?php echo '<?php $stringaCache include(\'".$path."\'); ?>' ?>";
					
					FilePutContentsAtomic($absolutePath."/DynamicFiles/".$fileNameRandom, $html);
					$this->dinamicFiles[] = $absolutePath."/DynamicFiles/".$fileNameRandom;
					return $absolutePath."/DynamicFiles/".$fileNameRandom;
				}
				else if ($cachedTemplateFile)
				{
					$path_parts = pathinfo($path);
					
					$fileName = $this->partialKey."_".$path_parts['basename'];
					
					$pathOfCachedTemplateFIle = $absolutePath."/Include/".$fileName;
// 					echo $pathOfCachedTemplateFIle;die();
					if (!file_exists($pathOfCachedTemplateFIle))
					{
						extract($this->_data);
						ob_start();
						include($path);
						$output = ob_get_clean();
						FilePutContentsAtomic($pathOfCachedTemplateFIle, $output);
					}
					
					$html = "<?php echo '<?php include(\'".$pathOfCachedTemplateFIle."\'); ?>' ?>";
					
					FilePutContentsAtomic($absolutePath."/DynamicFiles/".$fileNameRandom, $html);
					$this->dinamicFiles[] = $absolutePath."/DynamicFiles/".$fileNameRandom;
					return $absolutePath."/DynamicFiles/".$fileNameRandom;
				}
				else
					return $path;
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
		
		if (!self::$folderIncludeChecked && !is_dir($absolutePath."/Include") && $this->folder)
		{
			createFolderFull($this->folder."/Include", $this->absolutePath);
			
			self::$folderIncludeChecked = true;
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
			
			FilePutContentsAtomic($absolutePath."/".$fileName, $html);
			
			array_map('unlink', $this->dinamicFiles);
			
			return $absolutePath."/".$fileName;
		}
		
		return null;
	}
}
