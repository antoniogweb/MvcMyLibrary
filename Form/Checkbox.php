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

/** create the HTML of an input text entry */
class Form_Checkbox extends Form_Entry
{

	public function __construct($entryName = null)
	{
		$this->entryName = $entryName;
	}

	public function render($value = null)
	{
		$wrap = $this->getWrapElements($value);
		$returnString = $wrap[0];
		$returnString .= "<div class='".$this->getEntryClass()."' ".$this->getEntryAttributes().">\n\t";
		$returnString .= $wrap[1];
		$returnString .= $this->getLabelTag();
		$returnString .= $wrap[2];
		
		$entryValue = $this->fill ? $value : null;
		
		$returnString .= Html_Form::checkbox($this->entryName, $entryValue, $this->options, $this->className,$this->idName,$this->attributes);
		$returnString .= $wrap[3];
		$returnString .="</div>\n";
		$returnString .= $wrap[4];
		return $returnString;
	}

}
