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

class Cache_Db {
	
	public static $cachedQueries = array();
	public static $cachedTables = array();
	public static $cacheFolder = null;
// 	public static $cacheFolderRootPath = null;
// 	public static $cacheFolderRelativePath = null;
	public static $cacheMinutes = 10;
	public static $cacheTimeString = null;
	public static $cacheUnixTime = 0;
	public static $cleanCacheEveryXMinutes = 60;
	public static $folderExists = false;
	public static $maxNumberOfFilesCached = 0; // if 0, there is no limit
	public static $skipWritingCache = false; // if true, the cache won't be written
	public static $skipReadingCache = false; // if true, the cache won't be read
	public static $useRandomPeriods = false;
	public static $minutesOfPeriod = 10;
	public static $folderCookieName = "cookie_folder";
	
	public static function roundToLastSet()
	{
		$date = new DateTime();
		
		// Remove any seconds, we don't need them
		$seconds = $date->format('s');
		$date->modify('-' . $seconds . ' seconds');
		
		// Store the original number of minutes on the datetime
		$original_minutes = $date->format('i');
		
		if (!self::$useRandomPeriods)
		{
			$remaining_minutes = $original_minutes - ($original_minutes - ($original_minutes % self::$cacheMinutes));
			
			$date->modify('-' . $remaining_minutes . ' minutes');
		}
		else
		{
			$randomMultiplicator = random_int(1, floor(self::$cacheMinutes / self::$minutesOfPeriod));
			
			$remaining_minutes = $original_minutes - ($original_minutes - ($original_minutes % self::$minutesOfPeriod));
			
			$date->modify('-' . $remaining_minutes . ' minutes');
			
			$remaining_minutes = $randomMultiplicator * self::$minutesOfPeriod;
			
			$date->modify('+' . $remaining_minutes . ' minutes');
		}
		
		return $date;
	}
	
	public static function deleteExpired($force = false)
	{
		$path = self::$cacheFolder."/last_clean.txt";
		
		$cacheDuration = !self::$useRandomPeriods ? self::$cacheMinutes : 0;
		
		if (file_exists(self::$cacheFolder))
		{
			if (file_exists($path))
			{
				$time = (int)file_get_contents($path);
				
				if ((time() - $time) >= 60 * self::$cleanCacheEveryXMinutes)
				{
					foreach (new DirectoryIterator(self::$cacheFolder) as $fileInfo)
					{
						if ($fileInfo->isDot())
							continue;
						
						if ($fileInfo->getFilename() == "index.html" || $fileInfo->getFilename() == ".htaccess")
							continue;
						
						if ($force || ($fileInfo->isDir() && ((time() - (int)$fileInfo->getFilename()) >= (60 * $cacheDuration))))
						{
							$fName = $fileInfo->getRealPath();
							array_map('unlink', glob("$fName/*.*"));
							unlink("$fName/.htaccess");
							rmdir($fName);
						}
					}
					
					file_put_contents($path, time());
				}
			}
			else
				file_put_contents($path, time());
		}
	}
	
	public static function getCacheUnixTime()
	{
		self::getCacheTimeString();
		
		return self::$cacheUnixTime;
	}
	
	public static function getCacheTimeString()
	{
		if (!self::$cacheUnixTime)
		{
			if (
				self::$useRandomPeriods && 
				isset($_COOKIE[self::$folderCookieName]) && 
				$_COOKIE[self::$folderCookieName] && 
				is_numeric($_COOKIE[self::$folderCookieName]) && 
				(int)$_COOKIE[self::$folderCookieName] > time() && 
				@is_dir(rtrim(self::$cacheFolder,"/")."/".(int)basename($_COOKIE[self::$folderCookieName]))
			)
			{
				self::$cacheUnixTime = (int)basename($_COOKIE[self::$folderCookieName]);
			}
			else
			{
				$date = self::roundToLastSet();
				self::$cacheUnixTime = strtotime($date->format("Y-m-d H:i"));
				
				// save the cache folder in cookie
				if (self::$useRandomPeriods)
				{
					$_COOKIE[self::$folderCookieName] = self::$cacheUnixTime;
					$time = time() + (self::$cacheMinutes * 60);
					Cookie::set(self::$folderCookieName, self::$cacheUnixTime, $time);
				}
			}
		}
	}
	
	public static function getData($table, $query)
	{
		if (in_array($table, self::$cachedTables))
		{
			if (isset(self::$cachedQueries[md5($query)]))
				return self::$cachedQueries[md5($query)];
			else if (self::$cacheFolder)
			{
				if (self::$skipReadingCache)
					return null;
				
				$cacheFolderFull = rtrim(self::$cacheFolder,"/")."/".self::getCacheUnixTime();
				
				$fileName = md5($query).".txt";
				
				if (@is_file($cacheFolderFull."/".$fileName))
				{
					self::$cachedQueries[md5($query)] = unserialize(file_get_contents($cacheFolderFull."/".$fileName));
					
					return self::$cachedQueries[md5($query)];
				}
			}
		}
		
		return null;
	}
	
	public static function setData($table, $query, $data)
	{
		if (in_array($table, self::$cachedTables))
		{
			if (self::$cacheFolder)
			{
				if (self::$skipWritingCache)
					return;
				
				$cacheFolderFull = rtrim(self::$cacheFolder,"/")."/".self::getCacheUnixTime();
				
				if(!self::$folderExists && !is_dir($cacheFolderFull))
				{
					if (@mkdir(self::$cacheFolder))
					{
						$fp = fopen(self::$cacheFolder.'/index.html', 'w');
						fclose($fp);
						
						$fp = fopen(self::$cacheFolder.'/.htaccess', 'w');
						fwrite($fp, 'deny from all');
						fclose($fp);
					}
					
					if (@mkdir($cacheFolderFull))
					{
						$fp = fopen($cacheFolderFull.'/index.html', 'w');
						fclose($fp);
						
						$fp = fopen($cacheFolderFull.'/.htaccess', 'w');
						fwrite($fp, 'deny from all');
						fclose($fp);
					}
					
					self::$folderExists = true;
				}
				
				if(is_dir($cacheFolderFull))
				{
					if (self::$maxNumberOfFilesCached)
					{
						$iterator = new FilesystemIterator($cacheFolderFull, FilesystemIterator::SKIP_DOTS);
						$numberOfFilesCached = iterator_count($iterator);
						
						if ($numberOfFilesCached > self::$maxNumberOfFilesCached)
						{
							self::$skipWritingCache = true;
							return;
						}
					}
					
					$fileName = md5($query).".txt";
					
					file_put_contents($cacheFolderFull."/".$fileName, serialize($data));
					self::$cachedQueries[md5($query)] = $data;
				}
			}
			else
				self::$cachedQueries[md5($query)] = $data;
		}
	}
	
	public static function removeTablesFromCache($tables)
	{
		foreach ($tables as $table)
		{
			if (($key = array_search($table, self::$cachedTables)) !== false) {
				unset(self::$cachedTables[$key]);
			}
		}
	}
	
	public static function addTablesToCache($tables)
	{
		foreach ($tables as $table)
		{
			if (!in_array($table, Cache_Db::$cachedTables)) {
				Cache_Db::$cachedTables[] = $table;
			}
		}
	}
}
