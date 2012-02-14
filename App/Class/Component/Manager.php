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
	 * @desc Пути до подключенных компонент
	 * @var array of string
	 */
	protected $_components = array ();

	/**
	 *
	 * @param string $namespace
	 * @param string $dir
	 */
	public function addDirectory ($namespace, $dir)
	{
		$components = scandir ($dir);
		foreach ($components as $component)
		{
			if (ctype_alpha ($component))
			{
				$this->addComponent ($namespace, $dir, $component);
			}
		}
	}

	/**
	 *
	 * @param string $namespace
	 * @param string $dir
	 * @param string $component
	 */
	public function addComponent ($namespace, $dir, $component)
	{
		$ns = $namespace . '\\' . $component;
		$path = $dir . '/' . $component;
		$this->_components [$ns] = $path;
		Loader::addPath ($ns, $path . '/Class/');
	}

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
	 * @desc Возвращает путь до директории компонента
	 * @param string $name
	 * @return string
	 */
	public function get ($name)
	{
		return isset ($this->_components [$name])
			? $this->_components [$name]
			: null;
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
			foreach ($dirs as $dir)
			{
				$this->addDirectory ($namespace, $dir);
			}
		}
	}

}
