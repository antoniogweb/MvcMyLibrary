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

class Timer_Dev
{
	use Timer_Generic;
	
	public $absoluteLogPath = null; // absolute path (se $logFolder below)
	public $logFolder = "Logs"; // folder where the log files are saved, the path is relative path to the $absoluteLogPath path (see above), 
	public $logFile = "application_times.log";
	
	private static $instance = null; //instance of this class
	
	private $times = [];
	
	private function __construct($absoluteLogPath = null)
	{
		$this->setAbsolutePath($absoluteLogPath);
	}

	public static function getInstance($absoluteLogPath = null)
	{
		if (!isset(self::$instance)) {
			$className = __CLASS__;
			self::$instance = new $className($absoluteLogPath);
		}
		
		return self::$instance;
	}
	
	public function startTime($name, $signature)
	{
		$this->times[$name][md5($signature)]["query"] = $signature;
		$this->times[$name][md5($signature)]["start"] =  microtime(true);
	}
	
	public function endTime($name, $signature)
	{
		$this->times[$name][md5($signature)]["end"] = microtime(true);
		
		if (isset($this->times[$name][md5($signature)]["start"]))
			$this->times[$name][md5($signature)]["time"] = $this->times[$name][md5($signature)]["end"] - $this->times[$name][md5($signature)]["start"];
	}
}
