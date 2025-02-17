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

class Cache_Caller_Cache
{
	public $absoluteLogPath = null; // absolute path (se $logFolder below)
	public $logFolder = "Logs/CacheMethods"; // folder where the log files are saved, the path is relative path to the $absoluteLogPath path (see above), 
	
	private $methodCalls = []; // it contains all the calls and the results
	private $saveToDisk = false; // if it has to save the results on cached files
	private $maxNumberOfFilesCached = 0; // if 0, there is no limit
	
	public function __construct($saveToDisk = false, $maxNumberOfFilesCached = 0)
	{
		$this->absoluteLogPath = ROOT;
		$this->saveToDisk = $saveToDisk;
		$this->maxNumberOfFilesCached = $maxNumberOfFilesCached;
	}
	
	public function setSaveToDisk($saveToDisk)
	{
		$this->saveToDisk = $saveToDisk;
	}
	
	public function getMethodCalls()
	{
		return $this->methodCalls;
	}
	
	public function setMethodCalls($methodCalls)
	{
		$this->methodCalls = $methodCalls;
	}
	
	public function callMethod($obj, $methodName, $args)
	{
		$className = get_class($obj);
		
		$hash = md5(Params::$lang.$className.$methodName.serialize($args));
		
		if (isset($this->methodCalls[$hash]))
			return $this->methodCalls[$hash];
		else if ($this->saveToDisk && @is_file($this->absoluteLogPath."/".$this->logFolder."/$hash.log"))
		{
			$this->methodCalls[$hash] = unserialize(file_get_contents($this->absoluteLogPath."/".$this->logFolder."/$hash.log"));
			return $this->methodCalls[$hash];
		}
		
		$res = call_user_func_array(array($obj, $methodName), $args);
		
		$this->methodCalls[$hash] = $res;
		
		$this->saveCallsToFile($hash, $res);
		
		return $res;
	}
	
	// check the max number of files
	public function checkNumberOfFiles()
	{
		if ($this->maxNumberOfFilesCached)
		{
			$iterator = new FilesystemIterator($this->absoluteLogPath."/".$this->logFolder, FilesystemIterator::SKIP_DOTS);
			$numberOfFilesCached = iterator_count($iterator);
			
			if ($numberOfFilesCached > $this->maxNumberOfFilesCached)
				return false;
		}
		
		return true;
	}
	
	public function saveCallsToFile($hash, $res)
    {
		if (!$this->saveToDisk)
			return;
		
		if (!@is_dir($this->absoluteLogPath."/".$this->logFolder))
			createFolderFull($this->logFolder, $this->absoluteLogPath);
		
		if (!$this->checkNumberOfFiles())
			return;
		
		FilePutContentsAtomic($this->absoluteLogPath."/".$this->logFolder."/".$hash.".log", serialize($res));
    }
}
