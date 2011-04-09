<?php
/**
 * 
 * @desc Механизм определения роута по адресу
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Router
{
	
	/**
	 * @var Route
	 */
	private static $_route;
	
	/**
	 * @return Route
	 */
	public static function getRoute ()
	{
		return self::$_route;
	}
	
	/**
	 * @desc Разбирает запрос и извлекат параметры согласно
	 */
	public static function parse () 
	{
		$url = Request::uri ();

		$gets = Request::stringGet ();

		if ($gets)
		{
		    $gets = (array) explode ('&', $gets);
		    
		    foreach ($gets as $get)
		    {
			if (strpos ($get, '=') === false)
			{
			    $_GET ['get'] = 1;
			}
			else
			{
			    $tmp = explode ('=', $get);
			    $_GET [$tmp [0]] = $tmp [1];
			}
		    }
		}

		$url = $url ? $url : '/';
		
		$route = (array) explode ('/', trim ($url, '/'));
		
		self::$_route = self::locate ($url);
		
		if (!self::$_route)
		{
			return;
		}
		
		$parts = (array) explode ('/', trim (self::$_route->route, '/'));

		$len = min (sizeof ($route), sizeof ($parts));
		
		for ($i = 0; $i < $len; $i++)
		{
			$st = strpos ($parts [$i], ':');
			if ($st !== false)
			{
				Request::param (
					substr ($parts [$i], $st + 1), 
					isset ($route [$i]) ? substr ($route [$i], $st) : 0
				);
			}
		}
	}
	
	/**
	 * @desc 
	 * @param string $url
	 * @return Route
	 */
	public static function locate ($url)
	{
		$url = '/' . trim ($url, '/') . '/';
		
		/*
		 * Заменяем /12345678/ на /?/.
		 * Операция применяется дважды, т.к. если в запросе
		 * несколько чисел идет подряд "/content/123/456/789/",
		 * то в результате первого прохода вхождения будут заменены
		 * через раз - "/content/?/456/?/", и только после второго
		 * полностью - "/content/?/?/?/".
		 * Это позволяет привести все запросы с переменными к одному,
		 * который будет закеширован. 
		 */ 
		$template = preg_replace ('#/[0-9]{1,}/#i', '/?/', $url);
		$template = preg_replace ('#/[0-9]{1,}/#i', '/?/', $template);
		
		$select = new Query ();
		$select
			->select (array ('Route' => array ('id', 'route', 'View_Render__id')))
			->select (array ('View_Render' => array ('name' => 'viewRenderName')))
			->from ('Route')
			->from ('View_Render')
			->where ('? RLIKE template', $template)
			->where ('Route.View_Render__id = View_Render.id')
			->order (array ('weight' => Query::DESC))
			->limit (1);
		
		$row = DDS::execute ($select)->getResult ()->asRow ();
		//var_dump(DDS::getDataSource()->getQuery('Mysql'), $row);
		if (!$row)
		{
			return null;
		}

		Loader::load ('Route');
		return new Route ($row);
	}
	
}