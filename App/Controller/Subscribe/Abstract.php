<?php

class Controller_Subscribe_Abstract extends Controller_Abstract
{
	/**
	 * @desc Получить имя рассылки
	 * @return string
	 */
	protected function _subscribeName ()
	{
		return substr (get_class ($this), strlen ('Controller_Subscribe') + 1);
	}

	/**
	 * @desc Запуск рассылки
	 */
	public function backgroundStart ()
	{
		Loader::load ('Background_Agent_Manager');
		Background_Agent_Manager::instance ()->startAgent (
				self::BACKGROUND_AGENT,
				array (
						'Background_Agent_Resume__id'   => 0
				)
		);
	}

	/**
	 * @desc Продолжение процесса рассылки
	 */
	public function backgroundProcess ()
	{
		Loader::load ('Background_Agent_Manager');
		Background_Agent_Manager::instance ()->processAgent (
				self::BACKGROUND_AGENT
		);
	}
}