<?php

namespace Ice;

/**
 *
 * @desc Задание фронт контроллера
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Controller_Front_Task extends Controller_Task
{

	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		'controller'	=> 'Ice\\Front',
		'action'		=> 'index',
		'render'		=> 'Front',
		'template'		=> null
	);

	/**
	 * @desc Создает и возвращает экземпляр
	 */
	public function __construct ()
	{
		parent::__construct (new Controller_Action (array (
			'id'			=> null,
			'controller'	=> self::$_config ['controller'],
			'action'		=> self::$_config ['action']
		)));

		$config = $this->config ();

		$this->setViewRender (
			View_Render_Manager::byName ($config ['render'])
		);

		if ($config ['template'])
		{
			self::$_task->setTemplate ($config ['template']);
		}
	}

	/**
	 * @desc
	 * @return Objective
	 */
	public function config ()
	{
		return Config_Manager::get (get_class ($this), static::$_config);
	}

}
