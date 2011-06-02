<?php

/**
 * @desc Модель ячейки данных. Может быть уже заполнена
 * @author Роман Кузнецов, Колесников Илья
 * @package IcEngine
 */
class Model_Row extends Model
{
    protected static $_rows = array ();

	public function load ()
	{
		$rows = static::$_rows;

		if (isset ($rows [$this->key ()]))
		{
			return new self ($rows [$this->key ()]);
		}
		return parent::load ();
	}
}
