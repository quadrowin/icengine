<?php

namespace Ice;

/**
 *
 * @desc Расчитывает путь до шаблона
 *
 */
class Helper_Controller_Template
{

	/**
	 *
	 * @return Component_Manager
	 */
	protected function _getComponentManager ()
	{
		return Core::di ()->getInstance ('Ice\\Component_Manager', $this);
	}

	/**
	 * @desc
	 * @param string $controller Контроллер
	 * @param string $action Экшн
	 * @return string Путь
	 */
	public function get ($controller, $action)
	{
		$p = strrpos ($controller, '\\');

		if (false === $p)
		{
			return
				'Controller/' .
				str_replace ('_', '/', $controller) .
				'/' . $action;
		}

		$namespace = substr ($controller, 0, $p);
		$cname = substr ($controller, $p + 1);

		$component = $this->_getComponentManager ()->get ($namespace);

		if (!$component)
		{
			return
				'Controller/' .
				str_replace ('_', '/', $cname) . '/' . $action;
		}

		return
			$component .
			'/Resource/Template/Controller/' .
			str_replace ('_', '/', $cname) . '/' . $action;
	}

}
