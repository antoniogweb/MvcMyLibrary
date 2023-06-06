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

//class to create the database layer class
class Factory_Timer {

	private static $instance = null;
	
	//start the database connection
	//$prod: true or false
	//$arrayParams: array containing the $absolute log file path
	public static function getInstance($prod = true, $arrayParams = array())
	{
		if (isset(self::$instance))
			return self::$instance;
		
		if ($prod)
			self::$instance = call_user_func_array(array('Timer_Prod','getInstance'),$arrayParams);
		else
			self::$instance = call_user_func_array(array('Timer_Dev','getInstance'),$arrayParams);
		
		return self::$instance;
	}
}
