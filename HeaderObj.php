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

//manage the header
class HeaderObj {

	private $domainName; //the base path of the website (domainname)

	//redirect to $path after the time $time
	//string that appears until the page is redirected
	public function redirect($path,$time = 0,$string = null)
	{
		$completePath = Url::getRoot().$path;
		header('Refresh: '.$time.';url='.$completePath);
		if (isset($string)) echo $string;
		
		if (isset(Params::$logFunctionBeforeRedirect))
			call_user_func(Params::$logFunctionBeforeRedirect);
		
		exit;
	}
	
	// Redirect to $urlRedirect
	public static function location($urlRedirect)
	{
		header('Location: '.$urlRedirect);
		die();
	}
}
