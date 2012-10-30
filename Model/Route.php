<?php

/**
 * Роут
 *
 * @author goorus, morph
 */
class Route extends Model_Child
{
	private static $fromConfigLoaded;
	
	private static $list;

	/**
	 * Сформировать роут экшины, привязаннык роуту
	 *
	 * @return Route_Action_Collection
	 */
	public function actions()
	{
		Loader::load('Route_Action');
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
	 * Получить роут по урлу
	 *
	 * @param string $url
	 * @return Route
	 */
	public static function byUrl ($url)
	{
		$url = '/' . ltrim($url, '/');
		$provider = Data_Provider_Manager::get('Route_Cache');
		$route = $provider->get($url);
		if ($route) {
			return $route ? new self($route) : null;
		}
		$config = Config_Manager::get(__CLASS__);
		$emptyRoute = $config['empty_route']->__toArray();
		$row = null;
		$routes = self::getList();
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
		$provider->set($url, $row);
		//Resource_Manager::set('Route_Cache', $pattern, $row);
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
			$moduleCollection = Model_Collection_Manager::create(
				'Module'
			)->addOptions(array(
				'name'	=> 'Main',
				'value'	=> false
			));
			if ($moduleCollection) {
				$currentRoutes = array();
				foreach (self::$list as $route) {
					$currentRoutes[$route['route']] = 1;
				}
				foreach ($moduleCollection as $module) {
					$moduleConfig = Config_Manager::byPath('Route', $module->name);
					if (!$moduleConfig) {
						continue;
					}
					foreach ($moduleConfig['routes']->__toArray() as $route) {
						if (!isset($currentRoutes[$route['route']])) {
							$route['params']['module'] = $module->name;
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
