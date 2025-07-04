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

if (!defined('LIBRARY'))
	define('LIBRARY', ROOT);

/* SANITIZE SUPERGLOBAL ARRAYS */
function sanitizeSuperGlobal()
{
// 	$_GET = stripslashesDeep($_GET);
// 
// 	$_POST   = stripslashesDeep($_POST);
// 
// 	$_COOKIE = stripslashesDeep($_COOKIE);
// 
// 	$_SERVER = stripslashesDeep($_SERVER);
}

function checkPostLength($checkArray = null)
{
	$a = isset($checkArray) ? $checkArray : $_POST;
	
	if (MAX_POST_LENGTH !== 0)
	{
		foreach ($a as $key => $value)
		{
			if (is_array($value))
			{
				checkPostLength($value);
			}
			else
			{
				if (strlen($value) > MAX_POST_LENGTH) die('the length of some of the $_POST values is too large');
			}
		}
	}
}

//remove elements that are arrays
//applied to $_POST and $_GET
function fixArray($array, $depth = 1)
{
	$temp = array();
	
	foreach ($array as $key => $value)
	{
		if (is_array($value))
		{
			if (defined("MULTI_DIMENSIONAL_QUERY_STRING") && is_array(MULTI_DIMENSIONAL_QUERY_STRING) && in_array($key, MULTI_DIMENSIONAL_QUERY_STRING) && (int)$depth === 1)
			{
				$value = fixArray($value, $depth+1);
				
				$temp[$key] = implode("|",$value);
			}
			else
				$temp[$key] = "";
		}
		else
			$temp[$key] = $value;
		
// 		$temp[$key] = is_array($value) ? "" : $value;
	}
	
	return $temp;
}

function checkRequestUriLength()
{
	if (MAX_REQUEST_URI_LENGTH !== 0)
	{
		if (strlen($_SERVER['REQUEST_URI']) > MAX_REQUEST_URI_LENGTH) die('the length of the REQUEST_URI is too large');
	}
}

function checkRegisterGlobals()
{
    if (ini_get('register_globals')) die('register globals is on: MvcMyLibrary works only with register globals off');
}

//geth the name of the current application used
function getApplicationName()
{
	if (isset(Params::$currentApplication))
	{
		return Params::$currentApplication;
	}
	return null;
}

//geth the path of the current application used
//add the trailing slash to the application name
function getApplicationPath()
{
	if (isset(Params::$currentApplication))
	{
		return "Apps".DS.ucfirst(Params::$currentApplication).DS;
	}
	
	return null;
}

// get all combinations of language and country
function createArrayLanguagesCountries()
{
	static $combinations = array();
	
	if ($combinations)
		return $combinations;
	
	if (Params::$frontEndLanguages)
	{
		if (count(Params::$frontEndCountries) > 0)
		{
			$combinations = array();
			
			foreach (Params::$frontEndLanguages as $lang)
			{
				foreach (Params::$frontEndCountries as $country)
				{
					$combinations[] = $lang . Params::$languageCountrySeparator . $country;
				}
			}
			
			return $combinations;
		}
		else
			return Params::$frontEndLanguages;
	}
	
	return array();
}

function languageInUrl($url)
{
	$url = trim($url,"/");
	
	if (in_array($url,createArrayLanguagesCountries()))
	{
		return $url."/";
	}
	return "";
}

function removeVirtualSubfolder($url)
{
	if (!defined('VIRTUAL_SUBFOLDERS'))
		return $url;
	
	$regExpr = '/^('.implode("|", VIRTUAL_SUBFOLDERS).')/';
	
	if (preg_match($regExpr,$url, $matches))
	{
		Url::$virtualFolder = $matches[1];
		
		$url = str_replace($matches[1]."/", "", $url);
	}
	
	return $url;
}

function callHook()
{
	$currentUrl = null;
	
	if (MOD_REWRITE_MODULE === true)
	{
		if (isset($_GET['url']))
		{
			if (defined("REMOVE_START_SLASH"))
				$_GET['url'] = ltrim($_GET['url'], "/");
			
			$_GET['url'] = removeVirtualSubfolder($_GET['url']);
			
			if ($_GET['url'] && !languageInUrl($_GET['url']))
			{
				$url = $_GET['url'];
			}
			else
			{
				$url = languageInUrl($_GET['url']) . DEFAULT_CONTROLLER . '/' . DEFAULT_ACTION;
			}
		}
		else
		{
			$url = DEFAULT_CONTROLLER . '/' . DEFAULT_ACTION;
		}
	}
	else
	{
		$qString = getQueryString();
		
		if (strcmp($qString,"") !== 0)
		{
			$qString = removeVirtualSubfolder($qString);
			
			if ($qString && !languageInUrl($qString))
			{
				$url = $qString;
			}
			else
			{
				$url = languageInUrl($qString) . DEFAULT_CONTROLLER . '/' . DEFAULT_ACTION;
			}
		}
		else
		{
			$url = DEFAULT_CONTROLLER . '/' . DEFAULT_ACTION;
		}
	}
	
	$arriveUrl = $url;
	
	$urlArray = array();
	$urlArray = explode("/",$url);
	
	//get the language
	if (count(Params::$frontEndLanguages) > 0)
	{
		if (count(Params::$frontEndCountries) > 0)
		{
			if (in_array($urlArray[0],createArrayLanguagesCountries()))
			{
				list(Params::$lang, Params::$country) = explode(Params::$languageCountrySeparator, $urlArray[0]);
				Params::$lang = sanitizeAll(Params::$lang);
				Params::$country = sanitizeAll(Params::$country);
				array_shift($urlArray);
			}
			else
			{
				Params::$lang = Params::$defaultFrontEndLanguage;
				Params::$country = Params::$defaultFrontEndCountry;
			}
			
			Params::$langCountry = Params::$lang.Params::$languageCountrySeparator.Params::$country;
		}
		else
		{
			if (in_array($urlArray[0],Params::$frontEndLanguages))
			{
				Params::$lang = sanitizeAll($urlArray[0]);
				array_shift($urlArray);
			}
			else
			{
				Params::$lang = Params::$defaultFrontEndLanguage;
			}
			
			Params::$langCountry = Params::$lang;
		}
	}
	
	$url = implode("/",$urlArray);
	
// 	rewrite the URL
	if (Route::$rewrite === 'yes')
	{
		$res = rewrite($url);
		$url = $res[0];
		$currentUrl = $res[1];
	}
	
	$url = mapController($url);
	
// 	echo $url;
	
	$urlArray = explode("/",$url);
	$controller = DEFAULT_CONTROLLER;
	$action = DEFAULT_ACTION;
	
	
	//check if an application name is found in the URL
	if (isset(Params::$installed) and isset($urlArray[0]) and strcmp($urlArray[0],'') !== 0 and in_array($urlArray[0],Params::$installed))
	{
		Params::$currentApplication = strtolower(trim($urlArray[0]));
		
		array_shift($urlArray);
	}
	
	if (isset($urlArray[0]))
	{
		$controller = (strcmp($urlArray[0],'') !== 0) ? strtolower(trim($urlArray[0])) : DEFAULT_CONTROLLER;
	}

	array_shift($urlArray);

	if (isset($urlArray[0]))
	{
		$action = (strcmp($urlArray[0],'') !== 0) ? strtolower(trim($urlArray[0])) : DEFAULT_ACTION;
	}

	//set ERROR_CONTROLLER and ERROR_ACTION
	$errorController = ERROR_CONTROLLER !== false ? ERROR_CONTROLLER : DEFAULT_CONTROLLER;
	$errorAction = ERROR_ACTION !== false ? ERROR_ACTION : DEFAULT_ACTION;

	/*
		CHECK COUPLES CONTROLLER,ACTION
	*/
	if (!in_array('all',Route::$allowed))
	{
		$couple = "$controller,$action";
		if (getApplicationName() !== null)
		{
			$couple = getApplicationName().",".$couple;
		}
		if (!in_array($couple,Route::$allowed))
		{
			Params::$currentApplication = null;
			$controller = $errorController;
			$action = $errorAction;
			$urlArray = array();
		}
	}
	
	/*
	VERIFY THE ACTION NAME
	*/	
	if (method_exists('Controller', $action) or !ctype_alnum($action) or (strcmp($action,'') === 0))
	{
		Params::$currentApplication = null;
		$controller = $errorController;
		$action = $errorAction;
		$urlArray = array();
	}

	/*
	VERIFY THE CONTROLLER NAME
	*/
	if (!ctype_alnum($controller) or (strcmp($controller,'') === 0))
	{
		Params::$currentApplication = null;
		$controller = $errorController;
		$action = $errorAction;
		$urlArray = array();
	}

	//check that the controller class belongs to the application/controllers folder
	//otherwise set the controller to the default controller
	// 	if (!file_exists(ROOT.DS.APPLICATION_PATH.DS.'Controllers'.DS.ucwords($controller).'Controller.php') and !file_exists(ROOT.DS.APPLICATION_PATH.DS.getApplicationPath().'Controllers'.DS.ucwords($controller).'Controller.php'))
	$controllerFolders = array(ROOT.DS.APPLICATION_PATH.DS.getApplicationPath().'Controllers');
	$controllerFolders = array_merge($controllerFolders, Controller::$alternativeControllerFolders);
	
	$folderOk = false;
	
	foreach ($controllerFolders as $folder)
	{
		if (file_exists($folder.DS.ucwords($controller).'Controller.php'))
		{
			$folderOk = true;
			break;
		}
	}
	
// 	if (!file_exists(ROOT.DS.APPLICATION_PATH.DS.getApplicationPath().'Controllers'.DS.ucwords($controller).'Controller.php'))
	if (!$folderOk)
	{
		Params::$currentApplication = null;
		$controller = $errorController;
		$action = $errorAction;
		$urlArray = array();
	}

	//set the controller class to DEFAULT_CONTROLLER if it doesn't exists
	if (!class_exists(ucwords($controller).'Controller'))
	{
		Params::$currentApplication = null;
		$controller = $errorController;
		$action = $errorAction;
		$urlArray = array();
	}

	//set the action to DEFAULT_ACTION if it doesn't exists
	if (!method_exists(ucwords($controller).'Controller', $action))
	{
		Params::$currentApplication = null;
		$controller = $errorController;
		$action = $errorAction;
		$urlArray = array();
	}
	
	array_shift($urlArray);
	$queryString = $urlArray;
	//set the name of the application
	$controllerName = $controller;
	$controller = ucwords($controller);
	$model = $controller;
	$controller .= 'Controller';
	$model .= 'Model';

// 	echo $controller."-".$action;
	//include the file containing the set of actions to carry out before the initialization of the controller class
	Hooks::load(ROOT . DS . APPLICATION_PATH . DS . 'Hooks' . DS . 'BeforeInitialization.php');

	if (class_exists($controller))
	{
		$timer = Factory_Timer::getInstance();
		
		$timer->startTime("CONSTRUCTOR","CONSTRUCTOR");
		$dispatch = new $controller($model,$controllerName,$queryString, getApplicationName(), $action);
		$timer->endTime("CONSTRUCTOR","CONSTRUCTOR");
		
		//pass the action to the controller object
		$dispatch->action = $action;
		
		$dispatch->currPage = $dispatch->baseUrl.'/'.$dispatch->controller.'/'.$dispatch->action;
		if (isset($currentUrl))
		{
			$dispatch->currPage = $dispatch->baseUrl.'/'.$currentUrl;
		}
		
		//require the file containing the set of actions to carry out after the initialization of the controller class
		Hooks::load(ROOT . DS . APPLICATION_PATH . DS . 'Hooks' . DS . 'AfterInitialization.php');

		$templateFlag= true;
		
		$cache = Cache_Html::getInstance();
		$cache->baseUrl = rtrim(Url::getRoot(),"/");
		$dispatch->setCacheParameters();
		$cache->currPage = $dispatch->currPage;
		
		if (method_exists($dispatch, $action) and is_callable(array($dispatch, $action)))
		{
			//pass the action to the theme object
			$dispatch->theme->action = $action;
			$dispatch->theme->currPage = $dispatch->baseUrl.'/'.$dispatch->controller.'/'.$dispatch->action;
			if (isset($currentUrl))
			{
				$dispatch->theme->currPage = $dispatch->baseUrl.'/'.$currentUrl;
			}
			
			if ($cache->saved())
			{
				if ($cache->fileWithCommands)
					include($cache->fileWithCommands);
				
				$cache->setData($dispatch->theme->get());
				$cache->load();
			}
			else
			{
				$timer->startTime("METHOD","METHOD");
				call_user_func_array(array($dispatch,$action),$queryString);
				$timer->endTime("METHOD","METHOD");
			}
		}
		else
		{
			$templateFlag= false;
		}
		
		if ($templateFlag)
		{
			if (!$cache->saved())
				$dispatch->theme->render($cache, $timer);
		}
		
	}
	else
	{
		echo "<h2>the '$controller' controller is not present!</h2>";
	}

}

// map the controller
function mapController($url)
{
	if (property_exists('Route', 'controllersMap'))
	{
		foreach (Route::$controllersMap as $controller => $toController)
		{
			$regExpr = '/^'.$controller.'/';
			
			if (preg_match($regExpr,$url))
			{
				$nurl = preg_replace('/^'.$controller.'/',$toController,$url);
				return $nurl;
			}
		}
	}
	
	return $url;
}

//rewrite the URL
function rewrite($url)
{
	foreach (Route::$map as $key => $address)
	{
		$oldKey = $key;
		$key = str_replace('\/','/',$key);
		$key = str_replace('/','\/',$key);
		
		$regExpr = Params::$exactUrlMatchRewrite ? '/^'.$key.'$/' : '/^'.$key.'/';

		if (preg_match($regExpr,$url))
		{
			$nurl = preg_replace('/^'.$key.'/',$address,$url);
			return array($nurl,$oldKey);
// 			return preg_replace('/^'.$key.'/',$address,$url);
		}
	}
// 	return $url;
	return array($url,null);
}

function getQueryString()
{

	if (strstr($_SERVER['REQUEST_URI'],'index.php/'))
	{
		return Params::$mbStringLoaded === true ? mb_substr(mb_strstr($_SERVER['REQUEST_URI'],'index.php/'),10) : substr(strstr($_SERVER['REQUEST_URI'],'index.php/'),10);
	}

	return '';
}

// function __autoload($className)
function EG_autoload($className)
{
	$backupName = $className;

	if (strstr($className,'_'))
	{
		$parts = explode('_',$className);
		$className = implode(DS,$parts);
	}

	if (file_exists(LIBRARY . DS . 'Library' . DS . $className . '.php'))
	{
		require_once(LIBRARY . DS . 'Library' . DS . $className . '.php'); 
	}
	else if (getApplicationName() and file_exists(ROOT . DS . APPLICATION_PATH . DS . getApplicationPath() . 'Controllers' . DS . $backupName . '.php'))
	{
		require_once(ROOT . DS . APPLICATION_PATH . DS . getApplicationPath() . 'Controllers' . DS . $backupName . '.php');
	}
	else if (file_exists(ROOT . DS . APPLICATION_PATH . DS . 'Controllers' . DS . $backupName . '.php'))
	{
		require_once(ROOT . DS . APPLICATION_PATH . DS . 'Controllers' . DS . $backupName . '.php');
	}
	else if (getApplicationName() and file_exists(ROOT . DS . APPLICATION_PATH . DS . getApplicationPath() . 'Models' . DS . $backupName . '.php'))
	{
		require_once(ROOT . DS . APPLICATION_PATH . DS . getApplicationPath() . 'Models' . DS . $backupName . '.php');
	}
	else if (file_exists(ROOT . DS . APPLICATION_PATH . DS . 'Models' . DS . $backupName . '.php'))
	{
		require_once(ROOT . DS . APPLICATION_PATH . DS . 'Models' . DS . $backupName . '.php');
	}
	else if (getApplicationName() and file_exists(ROOT . DS . APPLICATION_PATH . DS . getApplicationPath() . 'Modules' . DS . $backupName . '.php'))
	{
		require_once(ROOT . DS . APPLICATION_PATH . DS . getApplicationPath() . 'Modules' . DS . $backupName . '.php');
	}
	else if (file_exists(ROOT . DS . APPLICATION_PATH . DS . 'Modules' . DS . $backupName . '.php'))
	{
		require_once(ROOT . DS . APPLICATION_PATH . DS . 'Modules' . DS . $backupName . '.php');
	}
	else if (getApplicationName() and file_exists(ROOT . DS . APPLICATION_PATH . DS . getApplicationPath() . 'Strings' . DS . $backupName . '.php'))
	{
		require_once(ROOT . DS . APPLICATION_PATH . DS . getApplicationPath() . 'Strings' . DS . $backupName . '.php');
	}
	else if (file_exists(ROOT . DS . APPLICATION_PATH . DS . 'Strings' . DS . $className . '.php'))
	{
		require_once(ROOT . DS . APPLICATION_PATH . DS . 'Strings' . DS . $className . '.php');
	}
	
}

try {

	spl_autoload_register('EG_autoload');
	
	// Custom autoload
	if (function_exists("Custom_autoload"))
		spl_autoload_register('Custom_autoload');
	
	// Save the originale $_GET and $_POST
	Params::$rawGET = $_GET;
	Params::$rawPOST = $_POST;
	
	$_POST = fixArray($_POST);
	$_GET = fixArray($_GET);
	
	//check the length of the $_POST values
	checkPostLength();
	
	//check the length of the REQUEST_URI
	if (!defined('APP_CONSOLE'))
		checkRequestUriLength();
	
	// create the logger
	$logProd =  defined("LOG_TIMES") ? false : true;
	
	$timer = Factory_Timer::getInstance($logProd,array(ROOT));
	
	$timer->startTime("APP","APP");
	
	//connect to the database
	Factory_Db::getInstance(DATABASE_TYPE,array(HOST,USER,PWD,DB));
	
	//set htmlentities charset
	switch (DEFAULT_CHARSET)
	{
		case 'SJIS':
			Params::$htmlentititiesCharset = 'Shift_JIS';
			break;
	}

	$allowedCharsets = array('UTF-8','ISO-8859-1','EUC-JP','SJIS');
	if (!in_array(DEFAULT_CHARSET,$allowedCharsets)) die('charset not-allowed');

	//check if the mbstring extension is loaded
	if (extension_loaded('mbstring'))
	{
		//set the internal encoding
		mb_internal_encoding(DEFAULT_CHARSET);
		Params::$mbStringLoaded = true;
	}
	
	//load the files defined inside Config/Autoload.php
	foreach (Autoload::$files as $file)
	{
		$extArray = explode('.', $file);
		$ext = strtolower(end($extArray));
		
		$path = ROOT . DS . APPLICATION_PATH . DS . 'Include' . DS . $file;
		if (file_exists($path) and $ext === 'php')
		{
			require_once($path);
		}
	}

	//report errors
	ErrorReporting();
	
	//include the file containing the set of actions to carry out before the check of the super global array
	Hooks::load(ROOT . DS . APPLICATION_PATH . DS . 'Hooks' . DS . 'BeforeChecks.php');

	//sanitize super global arrays
	sanitizeSuperGlobal();

	//verify that register globals is not active
	checkRegisterGlobals();
	
	//call the main hook
	if (!defined('APP_CONSOLE'))
		callHook();
	
	//include the file containing the set of actions to carry out before ending application
	if (file_exists(ROOT . DS . APPLICATION_PATH . DS . 'Hooks' . DS . 'BeforeEnding.php'))
		Hooks::load(ROOT . DS . APPLICATION_PATH . DS . 'Hooks' . DS . 'BeforeEnding.php');
	
	//disconnect to the database
	if (!defined('APP_CONSOLE'))
		Factory_Db::disconnect(DATABASE_TYPE);

} catch (Exception $e) {

	echo '<div class="alert">Message: '.$e->getMessage().'</div>';

}
