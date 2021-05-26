<?php

// MvcMyLibrary is a PHP framework for creating and managing dynamic content
//
// Copyright (C) 2009 - 2021  Antonio Gallo (info@laboratoriolibero.com)
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

require_once (ROOT . DS . 'Config' . DS . 'Reporting.php');
require_once (ROOT . DS . 'Config' . DS . 'Restricted.php');
require_once (ROOT . DS . 'Config' . DS . 'Autoload.php');
require_once (LIBRARY . DS . 'Library' . DS . 'Functions.php');
require_once (LIBRARY . DS . 'Library' . DS . 'Strings' . DS . 'Functions.php');
require_once (LIBRARY . DS . 'Library' . DS . 'ErrorReporting.php');
require_once (LIBRARY . DS . 'Library' . DS . 'Call.php');
