<?php

// MvcMyLibrary is a PHP framework for creating and managing dynamic content
//
// Copyright (C) 2009 - 2023  Antonio Gallo (info@laboratoriolibero.com)
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

trait Timer_Generic
{
	public function getTimes()
	{
		return $this->times;
	}
	
	public function getTotals()
	{
		$totals = array();
		
		foreach ($this->times as $name => $struct)
		{
			$totals[$name] = array(
				"time"	=>	0,
				"number"=>	0,
			);
			
			foreach ($struct as $signature => $values)
			{
				if (isset($values["time"]))
					$totals[$name]["time"] += $values["time"];
				
				$totals[$name]["number"] += 1;
			}
		}
		
		return $totals;
	}
	
	public function writeLog()
	{
		Files_Log::$logFolder = $this->absoluteLogPath."/".$this->logFolder;
		$log = Files_Log::getInstance($this->logFile);
		
		$text = "";
		
		if (isset($_SERVER["REQUEST_URI"]))
			$text .= "\n".$_SERVER["REQUEST_URI"]."\n";
		
		$totals = $this->getTotals();
		$text .= json_encode($totals, JSON_PRETTY_PRINT)."\n";
		
		$log->writeString($text);
	}
	
	public function setAbsolutePath($absoluteLogPath = null)
	{
		if (isset($absoluteLogPath))
			$this->absoluteLogPath = $absoluteLogPath;
		else
			$this->absoluteLogPath = ROOT;
	}
}
