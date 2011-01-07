<?php

include 'Paginator/Item.php';

class Paginator
{
	
	/**
	 * 
	 * @param string $prefix
	 * @param integer $full_count
	 * @return Paginator_Item
	 */
	public static function fromGet ($prefix = '', $full_count = 0)
	{
		return self::create (
			max (Request::get ('page'), 1),
			max (Request::get ('limit', 30), 10),
			$full_count
		);
	}
	
	/**
	 * 
	 * @param integer $page
	 * @param integer $page_limit
	 * @param integer $full_count
	 * @return Paginator_Item
	 */
	public static function create ($page, $page_limit = 30, $full_count = 0)
	{
		return new Paginator_Item ($page, $page_limit, $full_count);
	}
	
}