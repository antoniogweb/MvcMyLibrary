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

final class Session
{
	private static $stack = [];

	public static function open()
	{
		self::pushState();

		// Se la sessione non è attiva, aprila
		if (session_status() === PHP_SESSION_NONE)
			session_start();
	}

	public static function close()
	{
		self::pushState();

		// Se la sessione è attiva, chiudila (rilascia lock)
		if (session_status() === PHP_SESSION_ACTIVE)
			session_write_close();
	}

	public static function restore()
	{
		if (empty(self::$stack))
			return; // restore chiamato senza open/close

		$prev = array_pop(self::$stack);
		$cur  = session_status();

		// Ripristina lo stato precedente
		if ($prev === PHP_SESSION_ACTIVE && $cur !== PHP_SESSION_ACTIVE)
		{
			// era aperta, ora è chiusa -> riapri
			if ($cur === PHP_SESSION_NONE)
				session_start();
		} 
		elseif ($prev === PHP_SESSION_NONE && $cur === PHP_SESSION_ACTIVE)
		{
			// era chiusa, ora è aperta -> richiudi
			session_write_close();
		}
		// Se era DISABLED, non facciamo nulla (caso raro)
	}

	private static function pushState()
	{
		self::$stack[] = session_status();
	}
}