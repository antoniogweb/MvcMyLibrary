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

//class to help db layers to build queries
//singleton!
class Db_Generic
{
	public $logger = null; // instance of Db_Log_Dev or Db_Log_Prod
	
	public function createSelectQuery($table,$fields='*',$where=null,$group_by=null,$order_by=null,$limit=null,$on=array(),$using=array(),$join=array(), $forUpdateShare = null)
	{
		$maxValue = max(count((array)$on),count((array)$using),count((array)$join));

		$joinString = null;
		for ($i=0; $i < $maxValue; $i++)
		{
			$joinString .= isset($join[$i]) ? $this->getJoinString($join[$i]) : null;
			if (isset($using[$i]))
			{
				$joinString .= ' USING ('.$using[$i].')';
			}
			else if (isset($on[$i]))
			{
				$joinString .= ' ON '.$on[$i];
			}
		}
		
		if (isset($where))
		{
			$where='WHERE '.$where;
		}
		if (isset($order_by)) {
			$order_by='ORDER BY '.$order_by;
		}
		if (isset($group_by)) {
			$group_by='GROUP BY '.$group_by;
		}
		if (isset($limit)) {
			$limit='LIMIT '.$limit;
		}
		
		$forUpdateShareClause = "";
		
		if (isset($forUpdateShare))
		{
			if ($forUpdateShare == "UPDATE")
				$forUpdateShareClause = "FOR UPDATE";
			else if ($forUpdateShare == "SHARE")
				$forUpdateShareClause = "FOR SHARE";
		}
		
		$query="SELECT $fields FROM $table $joinString $where $group_by $order_by $limit $forUpdateShareClause;";
		
		return $query;
	}
	
	public function setLogger($prod = true)
	{
		$this->logger = $prod ? new Db_Log_Prod() : new Db_Log_Dev();
	}
}
