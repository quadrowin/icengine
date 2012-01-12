<?php

namespace Ice;

/**
 *
 * @desc Задание фронт контроллера
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Controller_Front_Task extends Task
{

	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		'controller' => 'Ice\\Front',
		'action' => 'index',
		'render' => 'Front',
		// Шаблон
		'template' => 'Controller/Front/index',
		// Название транспорта по умолчанию
		'input' => 'default_input'
	);

	/**
	 * @desc Создает и возвращает экземпляр
	 */
	public function __construct ()
	{
		$config = $this->config ();

		parent::__construct (
			'Controller',
			array (
				'controller' => $config ['controller'],
				'action' => $config ['action'],
				'name' => __CLASS__
			)
		);

		$this->_request->setInput (
			Data_Transport_Manager::get ($config ['input'])
		);

		$render = $config ['render'];

		if ($this->_request->isJsHttpRequest ())
		{
			$render = 'JsHttpRequest';
		}

		$this->_response->setExtra (array (
			'render' => $render,
			'template' => $config ['template']
		));
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
