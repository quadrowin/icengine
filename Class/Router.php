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

    private static $_urls = array ();
    
	/**
	 * @desc Обнулить текущий роут
	 */
	public static function clearRoute ()
	{
		self::$_route = null;
        foreach (self::$_urls as $url)
        {
            Resource_Manager::set ('Route_Cache', $url, null);
        }
	}

	/**
	 * @desc Разбирает запрос и извлекат параметры согласно
	 * @return Route
	 */
	public static function getRoute ()
	{
		if (is_null (self::$_route))
		{
			$url = Request::uri ();
            Loader::load ('Route');
            
            self::$_urls [] = Route::pattern ('/' . trim ($url, '/') . '/');

			$gets = Request::stringGet ();

			if ($gets)
			{
				$gets = (array) explode ('&', $gets);

				foreach ($gets as $get)
				{
					if (strpos ($get, '=') === false)
					{
						$_REQUEST ['get'] = $_GET ['get'] = 1;
					}
					else
					{
						$tmp = explode ('=', $get);
						$_REQUEST [$tmp [0]] = $_GET [$tmp [0]] = $tmp [1];
					}
				}
			}

			$url = $url ? $url : '/';

			$route = (array) explode ('/', trim ($url, '/'));

			
			self::$_route = Route::byUrl ($url);

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

			if (isset (self::$_route->params))
			{
				foreach (self::$_route->params as $param => $value)
				{
					Request::param ($param, $value);
				}
			}
		}
		return self::$_route;
	}

}