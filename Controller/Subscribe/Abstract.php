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
}