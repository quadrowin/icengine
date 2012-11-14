<?php

class SiteMap
{

	/**
	 * @return Route_Collection
	 */
	public static function asList ()
	{
		$routes = new Route_Collection ();
		$routes->
			where ('visible=1')->
			where ('viewRenderId=1');

		$routes->items ();
		$routes = Helper_Collection::sortByParent($routes);
		return $routes;
	}

}