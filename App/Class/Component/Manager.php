<?php

namespace Ice;

/**
 *
 * @desc Менеджер компонентов
 * @author Yury Shvedov
 *
 */
class Component_Manager
{

	/**
	 * @desc Config
	 * @var array|Objective
	 */
	protected $_config = array (
		// Директории с компонентами
		'dirs' => array (
			'Ice' => array (
				'{$ice}/Components'
			)
		)
	);

	/**
	 * @desc Конфиги менеджера
	 * @return Objective
	 */
	public function config ()
	{
		if (is_array ($this->_config))
		{
			$this->_config = Config_Manager::get (
				get_class ($this),
				$this->_config
			);
		}
		return $this->_config;
	}

	/**
	 * @desc Обзор директорий с компонентами и подключение путей в лоадер.
	 */
	public function init ()
	{
		$config = $this->config ();

		if (!$config ['dirs'])
		{
			return;
		}

		Loader::load ('Helper_Dir');

		foreach ($config ['dirs'] as $namespace => $dirs)
		{
			$dirs = Helper_Dir::solve ($dirs);
			$components = scandir ($dir);
			foreach ($components as $component)
			{
				if (ctype_alpha ($component))
				{
					Loader::addPath (
						$namespace . '\\' . $component,
						$dir . DIRECTORY_SEPARATOR . $component
					);
				}
			}
		}
	}

}
