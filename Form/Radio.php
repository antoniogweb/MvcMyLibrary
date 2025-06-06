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

//create the HTML of a radio entry
class Form_Radio extends Form_Entry
{

	public function __construct($entryName = null)
	{
		$this->entryName = $entryName;
	}

	public function render($value = null)
	{
		if ($this->report and $this->skipIfEmpty and strcmp($value,"") === 0) return "";
		
		$wrap = $this->getWrapElements($value);
		$returnString = $wrap[0];
		$returnString .= "<div class='".$this->getEntryClass()."' ".$this->getEntryAttributes().">\n\t";
		$returnString .= $wrap[1];
		$returnString .= $this->getLabelTag();
		$returnString .= $wrap[2];
		
		$entryValue = $this->fill ? $value : null;
		
		if ($this->report)
		{
			if (is_array($this->options) and strcmp($this->reverse,"Y") === 0 and isset($this->entryName[$entryValue]))
			{
				$entryValue = $this->entryName[$entryValue];
			}
			else if (is_array($this->options) and array_search($entryValue, $this->options))
			{
				$entryValue = array_search($entryValue, $this->options);
			}
			$returnString .= "<div class='report_field report_field_".$this->entryName."'>".$entryValue."</div>";
		}
		else
		{
			$returnString .= Html_Form::radio($this->entryName,$entryValue,$this->options,$this->className, 'after', $this->idName, $this->reverse, $this->attributes);
		}
		
		$returnString .= $wrap[3];
		$returnString .="</div>\n";
		$returnString .= $wrap[4];
		return $returnString;
	}

}
