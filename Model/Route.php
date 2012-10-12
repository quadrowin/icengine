<?php

/**
 * Роут
 *
 * @author goorus, morph
 */
class Route extends Model_Child
{
	/**
	 * Загружены ли роуты из конфига
	 *
	 * @var boolean
	 */
	protected static $fromConfigLoaded = false;

	/**
	 * Лист роутов
	 *
	 * @var array
	 */
	protected static $list;

	/**
	 * Сформировать роут экшины, привязаннык роуту
	 *
	 * @return Route_Action_Collection
	 */
	public function actions()
	{
		$i = 0;
		$actionCollection = Model_Collection_Manager::create (
			'Route_Action'
		)->reset ();
		$actions = is_object($this->_fields['actions'])
			? $this->_fields['actions']->__toArray()
			: (array) $this->_fields['actions'];
		foreach ($actions as $action => $assign) {
			if (is_numeric($action)) {
				if (is_scalar($assign)) {
					$action	= $assign;
					$assign = 'content';
				} else {
					$assign = reset($assign);
					$action = key($assign);
				}
			}
			$tmp = explode('/', $action);
			$controller = $tmp[0];
			$controllerAction = !empty($tmp[1]) ? $tmp[1] : 'index';
			$action = new Route_Action(array(
				'Controller_Action'	=> new Controller_Action(array(
					'controller'	=> $controller,
					'action'		=> $controllerAction
				)),
				'Route'				=> $this,
				'sort'				=> ++$i,
				'assign'			=> $assign
			));

			$actionCollection->add($action);
		}
		return $actionCollection;
	}

	/**
	 * Добавить роут
	 *
	 * @param array $route
	 */
	public static function addRoute($route)
	{
		self::$list[] = $route;
	}

	/**
	 * Получить роут по урлу
	 *
	 * @param string $url
	 * @return Route
	 */
	public static function byUrl($url)
	{
		$url = '/' . ltrim($url, '/');
		$route = Resource_Manager::get('Route_Cache', $url);
		if ($route) {
			return $route ? new self($route) : null;
		}
		$config = Config_Manager::get(__CLASS__);
		$emptyRoute = $config['empty_route']->__toArray();
		$routes = self::getList();
		$row = null;
		foreach ($routes as $route) {
			if (empty($route['route'])) {
				continue;
			}
			$pattern = '#^' . $route['route'] . '$#';
			if (!empty($route['patterns'])) {
				foreach ($route['patterns'] as $var => $routeData) {
					$replace = $routeData['pattern'];
					$var = '{$' . $var . '}';
					if (!empty($routeData['optional'])) {
						$replace = '(?:' . $replace . ')?';
					}
					$pattern = str_replace($var, $replace, $pattern);
				}
			}
			if (preg_match($pattern, $url) && (
				!$row || (int) $route['weight'] > (int) $row['weight']
			)) {
				$row = array_merge($emptyRoute, $route);
				$row['pattern'] = $pattern;
			}
		}
		Resource_Manager::set('Route_Cache', $url, $row);
		return $row ? new self($row) : null;
	}

	/**
	 * Получить список роутов
	 *
	 * @return array
	 */
	public static function getList()
	{
		if (!self::$fromConfigLoaded) {
			$config = Config_Manager::get(__CLASS__);
			self::$list = $config['routes']->__toArray();
			self::$fromConfigLoaded = true;
			$routerConfig = Config_Manager::get('Router');
			$modules = $routerConfig['modules']->__toArray();
			if ($modules) {
				$currentRoutes = array();
				foreach (self::$list as $route) {
					$currentRoutes[$route['route']] = 1;
				}
				foreach ($modules as $module) {
					$moduleConfig = Config_Manager::byPath($module . '__Route');
					foreach ($moduleConfig['routes']->__toArray() as $route) {
						if (!isset($currentRoutes[$route['route']])) {
							self::$list[] = $route;
						}
					}
				}
			}
		}
		return self::$list;
	}

	/**
	 * Возвращает объект рендера для роутера
	 *
	 * @return View_Render_Abstract
	 */
	public function viewRender()
	{
		if (!empty($this->params['View_Render__id'])) {
			$viewRenderId = $this->params['View_Render__id'];
		} else {
			$config = Config_Manager::get(__CLASS__);
			$viewRenderId = $config['empty_route']->params['View_Render__id'];
		}
		$render = Model_Manager::byKey('View_Render', $viewRenderId);
		return $render;
	}
}
