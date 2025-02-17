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

class Cache_Functions
{
	private static $instance = null; //instance of this class
	private $caller = null; // instance of Cache_Caller_NoCache or Cache_Caller_Cache
	
    private $objs = []; //instances of objects passed by means of ::load()
    private $lastClass = null; // it contains the last class loaded
	
	private function __construct() {}
	
	public static function getInstance($caller = null)
	{
		if (!isset(self::$instance)) {
			$className = __CLASS__;
			self::$instance = new $className();
			
			if (isset($caller))
				self::$instance->caller = $caller;
			else
				self::$instance->caller = new Cache_Caller_NoCache(false);
		}
		
		return self::$instance;
	}
	
	public function load($model)
	{
		$className = $this->lastClass = get_class($model);
		
		if (!isset($this->objs[$className]))
			$this->objs[$className] = $model;
		
		return self::$instance;
	}
	
	public function __call($methodName, $args)
	{
		if ($this->lastClass)
			return $this->caller->callMethod($this->objs[$this->lastClass], $methodName, $args);
		else
			throw new Exception('Error in <b>'.__METHOD__.'</b>: function <b>'.$methodName.'</b> does not exists.');
    }
    
    public function setSaveToDisk($saveToDisk)
	{
		$this->caller->setSaveToDisk($saveToDisk);
	}
}
