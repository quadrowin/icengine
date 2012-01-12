<?php

namespace Ice;

Loader::load ('Asset_Single_Collection');

/**
 *
 * @desc Коллекция балансов.
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Balance_Collection extends Asset_Single_Collection
{

	/**
	 * @desc Возвращает текущй баланс.
	 * @return float
	 */
	public function value ()
	{
		return $this->first ()->value;
	}

}