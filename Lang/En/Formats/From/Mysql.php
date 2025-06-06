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

class Lang_En_Formats_From_Mysql
{

	//convert the string from MySQL decimal format to En decimal format
	public function decimal($string)
	{
		return $string;
	}
	
	//convert the string from MySQL float format to En float format 
	public function float($string)
	{
		return $string;
	}
	
	//convert the string from MySQL double format to En double format 
	public function double($string)
	{
		return $string;
	}
	
	
	//convert the string from MySQL date format to En date format 
	public function date($date)
	{
		if (preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/',$date))
		{
			$dateArray = explode('-',$date);
			return $dateArray[1]."-".$dateArray[2]."-".$dateArray[0];
		}
		return $date;
	}
	
	//convert the string from MySQL enum format to En enum format 
	public function enum($string)
	{
		return $string;
	}
	
}
